@include('components.admin.layout', ['title' => 'Dashboard'])

<!-- KPI Cards Row -->
<div class="row mt-4">
    {{-- Today's Sales --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">আজের বিক্রয়</h5>
                <h2 class="card-title-display mb-0">{{ Order::whereDate('created_at', today())->count() }}</h2>
                <small class="text-muted">যesterdayের অর্ডার</small>
            </div>
        </div>
    </div>
    
    {{-- Today's Orders --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">আজের অর্ডার</h5>
                <h2 class="card-title-display mb-0">{{ Order::whereDate('created_at', today())->count() }}</h2>
                <small class="text-muted"> সংখ্যা</small>
            </div>
        </div>
    </div>
    
    {{-- Total Sales --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">মোট বিক্রয়</h5>
                <h2 class="card-title-display mb-0">{{ number_format(Order::sum('total'), 0, '.', ',') }}</h2>
                <small class="text-muted">টaka</small>
            </div>
        </div>
    </div>
    
    {{-- Total Orders --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">মোট অর্ডার</h5>
                <h2 class="card-title-display mb-0">{{ Order::count() }}</h2>
                <small class="text-muted">অর্ডার</small>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mt-4">
    {{-- Total Customers --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">মোট ক্রেতা</h5>
                <h2 class="card-title-display mb-0">{{ User::count() }}</h2>
                <small class="text-muted">ব্যক্তি</small>
            </div>
        </div>
    </div>
    
    {{-- Total Products --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">মোট পণ্য</h5>
                <h2 class="card-title-display mb-0">{{ Product::active()->count() }}</h2>
                <small class="text-muted">ক্রিয়াক্ত</small>
            </div>
        </div>
    </div>
    
    {{-- Low Stock --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">কম স্টক</h5>
                <h2 class="card-title-display mb-0">{{ Product::whereIn('stock_status', ['out_of_stock', 'low_stock'])->count() }}</h2>
                <small class="text-muted">পণ্য</small>
            </div>
        </div>
    </div>
    
    {{-- Pending Orders --}}
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted mb-0">প@endrl pending</h5>
                <h2 class="card-title-display mb-0"> {{-- Placeholder; replace with actual pending status count when Order constants defined --}}0 {{-- Fallback --}}</h2>
                <small class="text-muted">অর্ডার</small>
            </div>
        </div>
    </div>
</div>