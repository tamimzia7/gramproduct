<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 07 — কার্ট/ইচ্ছেতালিকা আর্কিটেকচার সংশোধন:
     * - carts: currency যোগ; প্রতি ইউজারে একটিই কার্ট (unique user_id)
     * - cart_items: শুধুমাত্র variant-ভিত্তিক (product_id বাদ, product_variant_id NOT NULL),
     *   unique [cart_id, product_variant_id] — একই ভ্যারিয়েন্টের duplicate row অসম্ভব
     * - wishlist_items: পণ্য-ভিত্তিক (product_variant_id বাদ), unique [user_id, product_id]
     *
     * ইনডেক্স-অর্ডার গুরুত্বপূর্ণ: পুরনো composite ফেলার *আগে* নতুন unique যোগ করতে হয়,
     * নইলে InnoDB user_id/cart_id FK-এর index-support হারিয়ে 1553 ছুঁড়ে দেয়।
     * প্রতিটি ধাপ guarded — আংশিক-প্রয়োগ হলেও পুনঃরানে সম্পূর্ণ হয়।
     */
    public function up(): void
    {
        // ---------- carts ----------
        if (! Schema::hasColumn('carts', 'currency')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('currency', 3)->default('BDT');
            });
        }

        // একই ইউজারের একাধিক active cart অসম্ভব (nullable unique → guest একাধিক থাকতে পারবে)
        $duplicateUserCarts = DB::table('carts')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicateUserCarts as $userId) {
            DB::table('carts')
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->skip(1)
                ->delete();
        }

        if (! $this->hasIndex('carts', 'carts_user_id_unique')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }

        // ---------- cart_items ----------
        // ভ্যারিয়েন্ট-হীন legacy row থাকলে বাদ (NOT NULL constraint-এর আগে)
        DB::table('cart_items')->whereNull('product_variant_id')->delete();

        // ১) নতুন unique আগে — cart_id FK-এর index-support নিশ্চিত করতে
        //    (পুরনো composite-এর leftmost prefix cart_id; আগে ফেললে InnoDB আটকায়)
        if (! $this->hasIndex('cart_items', 'cart_items_cart_id_product_variant_id_unique')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unique(['cart_id', 'product_variant_id']);
            });
        }

        // ২) এখন পুরনো composite নিরাপদে ফেলা যায়
        if ($this->hasIndex('cart_items', 'cart_items_cart_id_product_id_product_variant_id_unique')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique(['cart_id', 'product_id', 'product_variant_id']);
            });
        }

        // ৩) পুরনো product_variant_id FK (nullOnDelete/SET NULL) আগেই drop,
        //    নইলে MySQL NOT NULL-এ change করতে দেবে না (SET NULL FK-এর সাথে NOT NULL অসম্ভব)।
        if ($this->hasForeignKeyOnColumn('cart_items', 'product_variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
            });
        }

        // ৪) product_id বাদ (এর legacy index-ও সাথে ফেলা হয়) + variant NOT NULL
        if ($this->hasForeignKeyOnColumn('cart_items', 'product_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        if (Schema::hasColumn('cart_items', 'product_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });

            Schema::table('cart_items', function (Blueprint $table) {
                $table->unsignedBigInteger('product_variant_id')->change();
            });
        }

        // ৫) FK cascadeOnDelete — নতুন করে তৈরি।
        //    নোট: InnoDB `..._product_variant_id_foreign` INDEX-টিই FK-support হিসেবে
        //    পুনঃব্যবহার করে — dropForeign শুধু constraint ফেলে, index রাখে।
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')->on('product_variants')->cascadeOnDelete();
        });

        // ---------- wishlist_items ----------
        // ১) নতুন unique আগে — user_id FK support ধরে রাখতে
        if (! $this->hasIndex('wishlist_items', 'wishlist_items_user_id_product_id_unique')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id']);
            });
        }

        // ২) পুরনো composite + variant FK/column বাদ
        if ($this->hasIndex('wishlist_items', 'wishlist_items_user_id_product_id_product_variant_id_unique')) {
            if ($this->hasForeignKeyOnColumn('wishlist_items', 'product_variant_id')) {
                Schema::table('wishlist_items', function (Blueprint $table) {
                    $table->dropForeign(['product_variant_id']);
                });
            }
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'product_id', 'product_variant_id']);
            });
        }

        if (Schema::hasColumn('wishlist_items', 'product_variant_id')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->dropColumn('product_variant_id');
            });
        }
    }

    public function down(): void
    {
        // ---------- wishlist_items ----------
        // নতুন unique drop করে পুরনো composite ফেরানো
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['user_id', 'product_id', 'product_variant_id']);
        });

        // ---------- cart_items ----------
        // নতুন unique drop করে পুরনো composite ফেরানো
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_variant_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
        });

        // ---------- carts ----------
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_id_unique');
            $table->dropColumn('currency');
        });
    }

    /**
     * Laravel 11+ Schema::hasIndex — MySQL/SQLite compatible
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }

    private function hasForeignKeyOnColumn(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, $foreignKey['columns'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }
};
