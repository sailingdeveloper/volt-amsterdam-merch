<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('shop.order_confirmed') }}</title>
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

                    <!-- Success Icon & Title -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center;">
                            <div style="width: 64px; height: 64px; background-color: #dcfce7; border-radius: 50%; margin: 0 auto 24px; line-height: 64px;">
                                <span style="color: #16a34a; font-size: 32px;">✓</span>
                            </div>
                            <h2 style="margin: 0 0 12px; color: #111827; font-size: 24px; font-weight: bold;">{{ __('shop.order_confirmed') }}</h2>
                            <p style="margin: 0; color: #6b7280; font-size: 16px;">{{ __('shop.order_confirmed_message') }}</p>
                            <p style="margin: 16px 0 0; color: #7c3aed; font-size: 14px;">{{ __('shop.email_pickup_info') }}</p>
                        </td>
                    </tr>

                    <!-- Order Details -->
                    <tr>
                        <td style="padding: 20px 40px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9fafb; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 16px; color: #111827; font-size: 16px; font-weight: 600;">{{ __('shop.order_details') }}</h3>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">{{ __('shop.order_number') }}</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 500; text-align: right;">#{{ $order->order_number }}</td>
                                            </tr>
                                            @if($order->customer_name)
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">{{ __('shop.name') }}</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">{{ $order->customer_name }}</td>
                                            </tr>
                                            @endif
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

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 14px;">{{ __('shop.email_footer') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
