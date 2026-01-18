<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('shop.admin_new_order') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #7c3aed; padding: 32px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">Volt Amsterdam</h1>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center;">
                            <div style="width: 64px; height: 64px; background-color: #fef3c7; border-radius: 50%; margin: 0 auto 24px; line-height: 64px;">
                                <span style="color: #d97706; font-size: 32px;">📦</span>
                            </div>
                            <h2 style="margin: 0 0 12px; color: #111827; font-size: 24px; font-weight: bold;">{{ __('shop.admin_new_order') }}</h2>
                            <p style="margin: 0; color: #6b7280; font-size: 16px;">{{ __('shop.admin_order_message', ['number' => $order->order_number]) }}</p>
                        </td>
                    </tr>

                    <!-- Customer Information -->
                    <tr>
                        <td style="padding: 0 40px 20px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px; color: #1e40af; font-size: 16px; font-weight: 600;">{{ __('shop.admin_customer_info') }}</h3>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px; width: 120px;">{{ __('shop.name') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: 500;">{{ $order->customer_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">{{ __('shop.admin_email') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px;">
                                                    <a href="mailto:{{ $order->customer_email }}" style="color: #7c3aed; text-decoration: none;">{{ $order->customer_email ?? '-' }}</a>
                                                </td>
                                            </tr>
                                            @if($order->customer_phone)
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">{{ __('shop.admin_phone') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px;">
                                                    <a href="tel:{{ $order->customer_phone }}" style="color: #7c3aed; text-decoration: none;">{{ $order->customer_phone }}</a>
                                                </td>
                                            </tr>
                                            @endif
                                            @if($order->billing_address_line1)
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px; vertical-align: top;">{{ __('shop.admin_address') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px;">
                                                    {{ $order->billing_address_line1 }}<br>
                                                    @if($order->billing_address_line2){{ $order->billing_address_line2 }}<br>@endif
                                                    {{ $order->billing_postal_code }} {{ $order->billing_city }}<br>
                                                    {{ $order->billing_country }}
                                                </td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">{{ __('shop.admin_language') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px;">{{ $order->locale === 'nl' ? 'Nederlands' : 'English' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Order Details -->
                    <tr>
                        <td style="padding: 0 40px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px; color: #111827; font-size: 16px; font-weight: 600;">{{ __('shop.order_details') }}</h3>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">{{ __('shop.order_number') }}</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 500; text-align: right;">#{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">{{ __('shop.admin_order_date') }}</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">{{ $order->created_at->format('d-m-Y H:i') }}</td>
                                            </tr>
                                        </table>

                                        <!-- Order Items -->
                                        <div style="border-top: 1px solid #e5e7eb; margin: 16px 0; padding-top: 16px;">
                                            <h4 style="margin: 0 0 12px; color: #111827; font-size: 14px; font-weight: 600;">{{ __('shop.items') }}</h4>
                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                @foreach($order->item as $item)
                                                <tr>
                                                    <td style="padding: 6px 0; color: #374151; font-size: 14px;">
                                                        {{ $item->product?->name ?? 'Product' }}@if($item->size) ({{ $item->size }})@endif × {{ $item->quantity }}
                                                    </td>
                                                    <td style="padding: 6px 0; color: #374151; font-size: 14px; text-align: right;">
                                                        €{{ $item->formatted_total }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>

                                        <!-- Totals -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-top: 1px solid #e5e7eb; margin-top: 16px; padding-top: 16px;">
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">{{ __('shop.subtotal') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px; text-align: right;">€{{ $order->formatted_subtotal }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">{{ __('shop.processing_fee') }}</td>
                                                <td style="padding: 6px 0; color: #111827; font-size: 14px; text-align: right;">€{{ $order->formatted_fee }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0 0; color: #111827; font-size: 16px; font-weight: 600;">{{ __('shop.total') }}</td>
                                                <td style="padding: 12px 0 0; color: #111827; font-size: 16px; font-weight: 600; text-align: right;">€{{ $order->formatted_total }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- View Order Button -->
                    <tr>
                        <td style="padding: 0 40px 40px; text-align: center;">
                            <a href="{{ url('/admin/orders/' . $order->id) }}" style="display: inline-block; background-color: #7c3aed; color: #ffffff; font-size: 16px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 8px;">{{ __('shop.admin_view_order') }}</a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 14px;">{{ __('shop.admin_email_footer') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
