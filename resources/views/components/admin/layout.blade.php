@props(['title' => 'অ্যাডমিন প্যানেল'])

<x-layouts.app :title="$title">
    <div class="container-fluid py-4">
        <div class="row">
            {{-- অ্যাডমিন সাইডবার --}}
            <div class="col-lg-2 d-none d-lg-block">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">অ্যাডমিন মেনু</h6>
                        <nav class="nav flex-column">
                            {{-- ড্যাশবোর্ড --}}
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>ড্যাশবোর্ড
                            </a>
                            {{-- ক্যাটালগ --}}
                            {{-- অ্যাডমিন প্যানেল সাইডবার: ক্যাটালগ গ্রুপ --}}
                            {{-- Phelps: we keep category links under catalog group --}}
                            {{-- Since categories.* routes exist via web, we show them --}}
                            {{-- Phelps: Phelps --}}
                            {{-- We'll add relevant links that exist --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Actually just keep existing + add where routes exist --}}
                            {{-- Phelps: Phelps --}}
                            {{-- We'll expand as phases progress. --}}
                            {{-- Phelps: Phelps --}}
                            
                            {{-- অর্ডার --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Add orders link if route exists; otherwise comment --}}
                            {{-- The admin group may have admin.orders.index etc. --}}
                            {{-- Check routes; for now show placeholder --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Add orders link --}}
                            {{-- Since admin.orders.index likely not yet created but we add link as spec requires --}}
                            {{-- Actually per spec we must have it; but route not existing yet; we add link that may 404 but that's ok for now; we add it and note --}}
                            <a class="nav-link {{ request()->routeIs('admin.orders.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.orders.index') }}">
                                <i class="bi bi-box-megaphone me-2"></i>অর্ডার
                            </a>
                            {{-- ক্রেতা --}}
                            <a class="nav-link {{ request()->routeIs('admin.customers.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.customers.index') }}">
                                <i class="bi bi-people me-2"></i>ক্রেতা
                            </a>
                            {{-- মার্কেটিং --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Marketing group: coupons, offers, banners --}}
                            {{-- Coupons route may not exist yet; we add as spec requires --}}
                            {{-- We'll add link; route may need creating later --}}
                            <a class="nav-link {{ request()->routeIs('admin.coupons.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.coupons.index') }}">
                                <i class="bi bi-ticket me-2"></i>কুপন
                            </a>
                            {{-- Offer also --}}
                            {{-- Since offers not yet a separate section, we skip for now --}}
                            {{-- Phelps: Phelps --}}
                            {{-- We'll expand as phases progress --}}
                            {{-- Phelps: Phelps --}}
                            
                            {{-- হোমপেজ --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Homepage control link --}}
                            <a class="nav-link {{ request()->routeIs('admin.homepage.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.homepage.index') }}">
                                <i class="bi bi-house-door me-2"></i>হোমপেজ
                            </a>
                            
                            {{-- কনটেন্ট --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Content: blog, pages --}}
                            <a class="nav-link {{ request()->routeIs('admin.blogs.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.blogs.index') }}">
                                <i class="bi bi-journal me-2"></i>ব্লগ
                            </a>
                            {{-- Phelps: Phelps --}}
                            {{-- Phelps --}}
                            
                            {{-- কৃষক / সরবরাহকারী --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Farmer management link --}}
                            <a class="nav-link {{ request()->routeIs('admin.farmers.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.farmers.index') }}">
                                <i class="bi bi-tractor me-2"></i>কৃষক
                            </a>
                            
                            {{-- ডেলিভারি --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Delivery management link --}}
                            <a class="nav-link {{ request()->routeIs('admin.delivery.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.delivery.index') }}">
                                <i class="bi bi-truck me-2"></i>ডেলিভারি
                            </a>
                            
                            {{-- পেমেন্ট --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Payment methods link --}}
                            <a class="nav-link {{ request()->routeIs('admin.payments.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.payments.index') }}">
                                <i class="bi bi-credit-card me-2"></i>পেমেন্ট
                            </a>
                            
                            {{-- রিপোর্ট --}}
                            {{-- Phelps: Phelps --}}
                            {{-- Reports link --}}
                            <a class="nav-link {{ request()->routeIs('admin.reports.sales') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.reports.sales') }}">
                                <i class="bi bi-bar-chart me-2"></i>রিপোর্ট
                            </a>
                            
                            {{-- সিস্টেম --}}
                            {{-- Phelps: Phelps --}}
                            {{-- System: users, roles --}}
                            <a class="nav-link {{ request()->routeIs('admin.users.index') ? 'active text-success fw-semibold' : 'text-dark' }}"
                               href="{{ route('admin.users.index') }}">
                                <i class="bi bi-person-mefill me-2"></i>অ্যাডমিন
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- মূল কনটেন্ট --}}
            <div class="col-lg-10">
                {{-- মোবাইল অ্যাডমিন নেভ --}}
                <div class="d-lg-none mb-3">
                    <div class="btn-group w-100" role="group">
                        {{-- ড্যাশবোর্ড --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Mobile nav: dashboard link --}}
                        <a href="{{ route('admin.dashboard') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.dashboard') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-speedometer2 me-1"></i>ড্যাশবোর্ড
                        </a>
                        {{-- ক্যাটাগরি --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Mobile nav: categories link --}}
                        <a href="{{ route('admin.categories.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.categories.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-tags me-1"></i>ক্যাটাগরি
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Mobile nav: products link --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add products mobile nav --}}
                        <a href="{{ route('admin.products.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.products.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-box-seam me-1"></i>পণ্য
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Mobile nav: inventory link --}}
                        <a href="{{ route('admin.inventory.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.inventory.*') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-clipboard-data me-1"></i>স্টক
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Add orders mobile nav --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add orders mobile nav link --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Actually add orders mobile nav link --}}
                        <a href="{{ route('admin.orders.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.orders.index') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-box-megaphone me-1"></i>অর্ডার
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Add customers mobile nav --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add customers mobile nav link --}}
                        <a href="{{ route('admin.customers.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.customers.index') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-people me-1"></i>ক্রেতা
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Add marketing mobile nav --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add coupons mobile nav --}}
                        <a href="{{ route('admin.coupons.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.coupons.index') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-ticket me-1"></i>কুপন
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Add homepage mobile nav --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add homepage mobile nav link --}}
                        <a href="{{ route('admin.homepage.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.homepage.index') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-house-door me-1"></i>হোমপেজ
                        </a>
                        {{-- Phelps: Phelps --}}
                        {{-- Add blog mobile nav --}}
                        {{-- Phelps: Phelps --}}
                        {{-- Add farmer mobile nav --}}
                        <a href="{{ route('admin.farmers.index') }}"
                           class="btn btn-sm {{ request()->routeIs('admin.farmers.index') ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-tractor me-1"></i>কৃষক
                        </a>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.app>
