@extends('staff.layout')

@section('title', 'Danh sách đơn hàng')
@section('page-title', 'Danh sách đơn hàng')

@section('styles')
<style>
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eef0f4;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-group label { font-size: 11px; font-weight: 600; color: #8a8fa8; text-transform: uppercase; letter-spacing: .5px; }
    .filter-input {
        padding: 8px 12px;
        border: 1px solid #e0e4ee;
        border-radius: 8px;
        font-size: 13px;
        font-family: 'Poppins', sans-serif;
        color: #1a1a2e;
        background: #f8f9fc;
        outline: none;
        transition: border .15s;
    }
    .filter-input:focus { border-color: var(--primary); background: #fff; }

    .status-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .status-tab {
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid #e0e4ee;
        color: #666;
        background: #fff;
        transition: all .15s;
    }
    .status-tab:hover { border-color: var(--primary); color: var(--primary); text-decoration: none; }
    .status-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .status-tab .count {
        background: rgba(0,0,0,.1);
        border-radius: 10px;
        padding: 1px 7px;
        font-size: 11px;
        margin-left: 4px;
    }
    .status-tab.active .count { background: rgba(255,255,255,.25); }

    .order-type-icon { font-size: 16px; }

    .action-btn {
        font-size: 12px;
        padding: 5px 11px;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all .15s;
        white-space: nowrap;
    }
    .btn-view   { background: #f4f6fb; color: #555; }
    .btn-view:hover { background: #e8eaf0; color: #222; text-decoration: none; }
    .btn-next   { background: #eef4ff; color: #4d7cfe; }
    .btn-next:hover { background: #4d7cfe; color: #fff; }
    .btn-cancel { background: #fff0f0; color: #e53e3e; }
    .btn-cancel:hover { background: #e53e3e; color: #fff; }

    .pagination-wrapper { display: flex; justify-content: center; padding: 20px 0 0; }
    .pagination { display: flex; gap: 6px; list-style: none; margin: 0; padding: 0; }
    .page-item .page-link {
        padding: 7px 13px;
        border-radius: 8px;
        border: 1px solid #e0e4ee;
        color: #555;
        font-size: 13px;
        text-decoration: none;
        background: #fff;
        transition: all .15s;
    }
    .page-item.active .page-link { background: var(--primary); color: #fff; border-color: var(--primary); }
    .page-item .page-link:hover { border-color: var(--primary); color: var(--primary); }

    .customer-cell { display: flex; align-items: center; gap: 8px; }
    .customer-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        object-fit: cover;
    }
    .customer-name { font-size: 13px; font-weight: 500; }
    .customer-phone { font-size: 11px; color: #8a8fa8; }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #aaa;
    }
    .empty-state i { font-size: 48px; color: #ddd; margin-bottom: 12px; display: block; }
    .cancel-reason-help {
        font-size: 12px;
        color: #8a8fa8;
        margin-top: 8px;
    }
</style>
@endsection

@section('content')

{{-- Status tabs --}}
<div class="status-tabs">
    <a href="{{ route('staff.orders') }}"
       class="status-tab {{ !request('status') ? 'active' : '' }}">
        Tất cả <span class="count">{{ $statusCounts->sum() }}</span>
    </a>
    @foreach([
        'pending'    => 'Chờ xác nhận',
        'confirmed'  => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'ready'      => 'Sẵn sàng',
        'delivered'  => 'Hoàn thành',
        'cancelled'  => 'Đã hủy',
        'failed'     => 'Thất bại',
    ] as $key => $label)
    <a href="{{ route('staff.orders', array_merge(request()->query(), ['status' => $key])) }}"
       class="status-tab {{ request('status') === $key ? 'active' : '' }}">
        {{ $label }}
        @if(($statusCounts[$key] ?? 0) > 0)
            <span class="count">{{ $statusCounts[$key] }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('staff.orders') }}" class="filter-bar">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif

    <div class="filter-group" style="flex:1;min-width:200px;">
        <label>Tìm kiếm</label>
        <input type="text" name="search" class="filter-input"
               placeholder="Mã đơn hoặc tên khách..."
               value="{{ request('search') }}">
    </div>

    <div class="filter-group">
        <label>Loại đơn</label>
        <select name="type" class="filter-input">
            <option value="">Tất cả</option>
            <option value="delivery"  {{ request('type') === 'delivery'  ? 'selected' : '' }}>Giao hàng</option>
            <option value="in_store"  {{ request('type') === 'in_store'  ? 'selected' : '' }}>Tại quán</option>
        </select>
    </div>

    <button type="submit" class="btn-primary-staff" style="height:38px;">
        <i class="fas fa-search"></i> Lọc
    </button>
    <a href="{{ route('staff.orders') }}" class="btn-outline-staff" style="height:38px;">
        <i class="fas fa-redo"></i> Đặt lại
    </a>
</form>

{{-- Orders table --}}
<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>
            {{ $orders->total() }} đơn hàng
        </span>
        <a href="{{ route('staff.create-order') }}" class="btn-primary-staff" style="font-size:13px;padding:6px 14px;">
            <i class="fas fa-plus"></i> Tạo đơn tại quán
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Loại</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <span style="font-weight:700;color:var(--primary);">#{{ $order->id }}</span>
                    </td>
                    <td>
                        <div class="customer-cell">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'K') }}&background=d4813a&color=fff&size=64"
                                 class="customer-avatar" alt="">
                            <div>
                                <div class="customer-name">{{ $order->user->name ?? 'Khách vãng lai' }}</div>
                                <div class="customer-phone">{{ $order->user->phone ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($order->order_type === 'delivery')
                            <span class="order-type-icon" title="Giao hàng">🛵</span>
                            <span style="font-size:12px;">Giao hàng</span>
                        @else
                            <span class="order-type-icon" title="Tại quán">🏠</span>
                            <span style="font-size:12px;">Tại quán</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:13px;">{{ $order->items->count() }} món</span>
                        <div style="font-size:11px;color:#aaa;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $order->items->map(fn($i) => $i->product->name ?? '?')->join(', ') }}
                        </div>
                    </td>
                    <td>
                        <span style="font-weight:600;">{{ number_format($order->final_price, 0, ',', '.') }}đ</span>
                        @if($order->discount_amount > 0)
                        <div style="font-size:11px;color:#27ae60;">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</div>
                        @endif
                    </td>
                    <td>
                        @if($order->payment)
                            <div style="font-size:12px;">{{ $order->payment->method_label }}</div>
                            <span class="badge-status {{ $order->payment->status === 'paid' ? 'badge-delivered' : 'badge-pending' }}" style="font-size:10px;">
                                {{ $order->payment->status_label }}
                            </span>
                        @else
                            <span style="color:#aaa;font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span>
                    </td>
                    <td style="font-size:12px;color:#8a8fa8;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m H:i') }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('staff.order.detail', $order->id) }}" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            {{-- Next status button --}}
                            @if($order->payment && $order->payment->method === 'bank_transfer' && $order->payment->status !== 'paid')
                            <form action="{{ route('staff.order.payment.confirm', $order->id) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="action-btn btn-next">
                                    <i class="fas fa-money-check-alt"></i>
                                    Chuyển khoản thành công
                                </button>
                            </form>
                            @elseif(count($order->next_statuses) > 0)
                                <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;"
                                    data-status-reminder-form="true"
                                    data-order-id="{{ $order->id }}"
                                    data-next-status="{{ $order->next_statuses[0] }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $order->next_statuses[0] }}">
                                <button type="submit" class="action-btn btn-next">
                                    <i class="fas fa-arrow-right"></i>
                                    {{ match($order->next_statuses[0]) {
                                        'confirmed'  => 'Xác nhận',
                                        'processing' => 'Chuẩn bị',
                                        'ready'      => 'Sẵn sàng',
                                        'delivered'  => 'Hoàn thành',
                                        default      => 'Tiếp theo',
                                    } }}
                                </button>
                            </form>
                            @endif
                            {{-- Cancel button --}}
                            @if(in_array('cancelled', $order->next_statuses))
                                <button type="button" class="action-btn btn-cancel"
                                        data-cancel-order-trigger="true"
                                        data-order-id="{{ $order->id }}"
                                        data-order-action="{{ route('staff.order.status', $order->id) }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Không có đơn hàng nào</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f0f2f5;">
        {{ $orders->links('staff.pagination') }}
    </div>
    @endif
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid #f0f2f5;">
                <h5 class="modal-title" style="font-weight:700;">Chọn lý do hủy đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelOrderModalForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancelReasonSelect" class="form-label" style="font-weight:600;">Lý do hủy</label>
                        <select class="form-select" id="cancelReasonSelect" name="cancel_reason" required>
                            <option value="">Chọn lý do</option>
                            <option value="change_option">Thay đổi topping/kích cỡ</option>
                            <option value="no_longer_needed">Không còn nhu cầu mua</option>
                            <option value="other">Lý do khác</option>
                        </select>
                        <div class="cancel-reason-help">Nhân viên cần chọn lý do trước khi xác nhận hủy đơn.</div>
                    </div>
                    <div class="mb-0 d-none" id="cancelReasonOtherWrap">
                        <label for="cancelReasonOther" class="form-label" style="font-weight:600;">Nhập lý do khác</label>
                        <textarea class="form-control" id="cancelReasonOther" name="cancel_reason_other" rows="3" placeholder="Nhập lý do hủy đơn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f2f5;">
                    <button type="button" class="btn-outline-staff" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn-primary-staff">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const modalElement = document.getElementById('cancelOrderModal');
    const form = document.getElementById('cancelOrderModalForm');
    const reasonSelect = document.getElementById('cancelReasonSelect');
    const otherWrap = document.getElementById('cancelReasonOtherWrap');
    const otherInput = document.getElementById('cancelReasonOther');

    if (!modalElement || !form || !reasonSelect || !otherWrap || !otherInput) {
        return;
    }

    const cancelModal = new bootstrap.Modal(modalElement);

    function syncOtherReasonVisibility() {
        const isOther = reasonSelect.value === 'other';
        otherWrap.classList.toggle('d-none', !isOther);
        otherInput.required = isOther;
        if (!isOther) {
            otherInput.value = '';
        }
    }

    document.querySelectorAll('[data-cancel-order-trigger="true"]').forEach((button) => {
        button.addEventListener('click', () => {
            form.action = button.dataset.orderAction;
            reasonSelect.value = '';
            otherInput.value = '';
            syncOtherReasonVisibility();
            cancelModal.show();
        });
    });

    reasonSelect.addEventListener('change', syncOtherReasonVisibility);
    syncOtherReasonVisibility();
})();
</script>
@endsection
