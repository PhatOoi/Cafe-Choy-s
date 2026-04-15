@extends('staff.layout')

@section('title', 'Lịch sử tạo đơn')
@section('page-title', 'Lịch sử tạo đơn 1 tháng gần nhất')

@section('styles')
<style>
    .history-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) {
        .history-stats { grid-template-columns: 1fr; }
    }

    .history-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .history-stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8a8fa8;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .history-stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.1;
    }
    .history-stat-note {
        margin-top: 6px;
        font-size: 12px;
        color: #8a8fa8;
    }

    .day-block {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        margin-bottom: 18px;
        overflow: hidden;
    }
    .day-head {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .day-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .day-subtitle {
        font-size: 12px;
        color: #8a8fa8;
        margin-top: 3px;
    }
    .day-metrics {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .day-chip {
        background: #fff7ef;
        color: var(--primary);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .day-chip-secondary {
        background: #f4f6fb;
        color: #52607a;
    }
    .history-table {
        width: 100%;
        border-collapse: collapse;
    }
    .history-table th {
        text-align: left;
        font-size: 11px;
        color: #8a8fa8;
        letter-spacing: .05em;
        text-transform: uppercase;
        background: #f8f9fc;
        padding: 12px 16px;
        border-bottom: 1px solid #eef0f4;
    }
    .history-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f8;
        font-size: 14px;
        vertical-align: top;
    }
    .history-table tr:last-child td { border-bottom: none; }
    .history-order-id {
        font-weight: 700;
        color: var(--primary);
    }
    .history-products {
        font-size: 13px;
        color: #444;
        line-height: 1.5;
    }
    .history-products div + div {
        margin-top: 3px;
    }
    .empty-history {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        color: #9aa1b1;
    }
    .empty-history i {
        font-size: 42px;
        margin-bottom: 10px;
        color: #d8dce6;
        display: block;
    }
    .history-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-history-cancel {
        border: none;
        background: #fff1f2;
        color: #dc2626;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 600;
    }
    .btn-history-cancel:hover {
        background: #fee2e2;
    }
    .cancel-reason-help {
        font-size: 12px;
        color: #8a8fa8;
        margin-top: 8px;
    }
</style>
@endsection

@section('content')
<div class="history-stats">
    <div class="history-stat">
        <div class="history-stat-label">Tổng số đơn</div>
        <div class="history-stat-value">{{ $summary['total_orders'] }}</div>
        <div class="history-stat-note">Trong {{ $summary['range_label'] }}</div>
    </div>
    <div class="history-stat">
        <div class="history-stat-label">Doanh thu nhân viên tạo</div>
        <div class="history-stat-value">{{ number_format($summary['total_revenue'], 0, ',', '.') }}đ</div>
        <div class="history-stat-note">Đơn tại quán do nhân viên tạo</div>
    </div>
    <div class="history-stat">
        <div class="history-stat-label">Doanh thu user tự mua</div>
        <div class="history-stat-value">{{ number_format($summary['customer_revenue'], 0, ',', '.') }}đ</div>
        <div class="history-stat-note">Đơn của khách hàng tự thanh toán</div>
    </div>
    <div class="history-stat">
        <div class="history-stat-label">Tiền mặt / Chuyển khoản</div>
        <div class="history-stat-value" style="font-size:20px;">{{ number_format($summary['cash_revenue'], 0, ',', '.') }}đ / {{ number_format($summary['transfer_revenue'], 0, ',', '.') }}đ</div>
        <div class="history-stat-note">Phân loại theo phương thức thanh toán</div>
    </div>
    <div class="history-stat">
        <div class="history-stat-label">Số ngày có phát sinh</div>
        <div class="history-stat-value">{{ $summary['active_days'] }}</div>
        <div class="history-stat-note">Tổng doanh thu gộp: {{ number_format($summary['combined_revenue'], 0, ',', '.') }}đ</div>
    </div>
</div>

@if($dailyGroups->isEmpty())
    <div class="empty-history">
        <i class="fas fa-receipt"></i>
        <div style="font-size:16px;font-weight:600;">Chưa có lịch sử tạo đơn trong 1 tháng gần nhất</div>
        <div style="font-size:13px;margin-top:6px;">Sau 1 tháng, lịch sử cũ sẽ không còn hiển thị ở đây.</div>
    </div>
@else
    @foreach($dailyGroups as $group)
        <div class="day-block">
            <div class="day-head">
                <div>
                    <div class="day-title">{{ $group['date']->format('d/m/Y') }}</div>
                    <div class="day-subtitle">Ngày {{ $group['date']->isoFormat('dddd') }}</div>
                </div>
                <div class="day-metrics">
                    <div class="day-chip">Đã bán {{ $group['total_orders'] }} đơn</div>
                    <div class="day-chip">Tổng tiền {{ number_format($group['total_revenue'], 0, ',', '.') }}đ</div>
                    <div class="day-chip day-chip-secondary">Nhân viên tạo {{ number_format($group['staff_created_revenue'], 0, ',', '.') }}đ</div>
                    <div class="day-chip day-chip-secondary">User tự mua {{ number_format($group['customer_revenue'], 0, ',', '.') }}đ</div>
                    <div class="day-chip day-chip-secondary">Tiền mặt {{ number_format($group['cash_revenue'], 0, ',', '.') }}đ</div>
                    <div class="day-chip day-chip-secondary">Chuyển khoản {{ number_format($group['transfer_revenue'], 0, ',', '.') }}đ</div>
                </div>
            </div>

            @if($group['orders']->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Sản phẩm</th>
                                <th>Tổng giá</th>
                                <th>Thanh toán</th>
                                <th>Thời gian</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['orders'] as $order)
                                <tr>
                                    <td><span class="history-order-id">#{{ $order->id }}</span></td>
                                    <td>
                                        <div class="history-products">
                                            @foreach($order->items as $item)
                                                <div>{{ $item->product->name ?? 'Sản phẩm đã xóa' }} x{{ $item->quantity }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ number_format($order->final_price, 0, ',', '.') }}đ</td>
                                    <td>{{ $order->payment->method_label ?? '—' }}</td>
                                    <td>{{ $order->created_at->format('H:i') }}</td>
                                    <td>
                                        <div class="history-actions">
                                            <a href="{{ route('staff.order.detail', $order->id) }}" class="btn-outline-staff" style="font-size:12px;padding:6px 12px;">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                            @if(in_array('cancelled', $order->next_statuses))
                                                <button type="button"
                                                        class="btn-history-cancel"
                                                        data-cancel-order-trigger="true"
                                                        data-order-action="{{ route('staff.order.status', $order->id) }}">
                                                    <i class="fas fa-times"></i> Hủy đơn
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:18px 20px;font-size:13px;color:#8a8fa8;">
                    Không có đơn tại quán do nhân viên này tạo trong ngày này. Phần trên chỉ hiển thị tổng doanh thu ngày theo yêu cầu.
                </div>
            @endif
        </div>
    @endforeach
@endif

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