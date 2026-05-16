<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #F8FAFC; font-family: 'Inter', Helvetica, Arial, sans-serif; color: #0F172A; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    
                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <h1 style="color: #FF8C00; font-size: 28px; margin: 0; font-weight: 800; letter-spacing: -0.5px;">Tontine<span style="color: #1E293B;">Chain</span></h1>
                            <p style="color: #475569; font-size: 14px; margin-top: 5px; font-weight: 500;">Finance Inclusive & Blockchain</p>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <div style="height: 1px; width: 40px; background-color: #FF8C00; margin: 0 auto 30px auto;"></div>
                            <h2 style="font-size: 20px; font-weight: 700; color: #1E293B; margin-bottom: 20px;">{{ $title }}</h2>
                            
                            <p style="font-size: 15px; color: #475569; line-height: 1.8; margin-bottom: 30px; text-align: left;">
                                {!! nl2br(e($content)) !!}
                            </p>

                            @if(isset($actionUrl) && $actionUrl)
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}" style="display: inline-block; background-color: #FF8C00; color: #FFFFFF; padding: 16px 32px; border-radius: 14px; text-decoration: none; font-size: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(255, 140, 0, 0.2);">
                                            Accéder à mon espace
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px;">
                            <p style="font-size: 12px; color: #94A3B8; font-weight: 500;">
                                &copy; {{ date('Y') }} TontineChain • Excellence & Inclusion
                            </p>
                            <p style="font-size: 11px; color: #CBD5E1; margin-top: 10px;">
                                Vous recevez cet email car vous êtes membre de la communauté TontineChain.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
