<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="background-color: #000000; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #ffffff; margin: 0; padding: 0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #000000;">
        <tr>
            <td align="center" style="padding: 60px 20px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 16px;">
                    
                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <h1 style="color: #d4af37; font-size: 24px; margin: 0; letter-spacing: 4px; font-weight: 300; text-transform: uppercase;">TontineChain</h1>
                            <div style="height: 1px; width: 30px; background-color: #d4af37; margin: 20px auto;"></div>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <h2 style="font-size: 18px; font-weight: 400; color: #ffffff; margin-bottom: 20px;">{{ $title }}</h2>
                            
                            <p style="font-size: 14px; color: #888888; line-height: 1.8; margin-bottom: 30px; text-align: left;">
                                {!! nl2br(e($content)) !!}
                            </p>

                            @if(isset($actionUrl) && $actionUrl)
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}" style="display: inline-block; background-color: #d4af37; color: #000000; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                            Accéder à l'espace
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px; border-top: 1px solid #1a1a1a; padding-top: 20px;">
                            <p style="font-size: 10px; color: #333333; text-transform: uppercase; letter-spacing: 2px;">
                                Excellence • Sécurité • Inclusion
                            </p>
                            <p style="font-size: 9px; color: #222222; margin-top: 10px;">
                                Vous recevez cet email car vous êtes membre du cercle d'excellence TontineChain.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
