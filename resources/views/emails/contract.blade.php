<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="background-color: #050505; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #e5e7eb; margin: 0; padding: 40px 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #0c0c0c; border: 1px solid #222; border-radius: 24px; overflow: hidden; padding: 40px;">
        
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="color: #d4af37; font-size: 24px; margin: 0; letter-spacing: 2px; font-weight: bold;">TONTINECHAIN</h1>
            <p style="color: #6b7280; font-size: 12px; text-transform: uppercase; margin-top: 5px;">Confirmation Officielle</p>
        </div>

        <div style="margin-bottom: 40px;">
            <h2 style="color: #10b981; font-size: 20px; margin-bottom: 16px;">{{ __('messages.contract_subject') }}</h2>
            <p style="font-size: 15px; color: #9ca3af; line-height: 1.6; margin-bottom: 24px;">
                {{ __('messages.contract_body') }}
            </p>

            <div style="background-color: #111; border-left: 4px solid #d4af37; padding: 20px; border-radius: 8px;">
                <h4 style="color: #fff; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase;">Détails de la Tontine "{{ $group->name }}"</h4>
                <table style="width: 100%; font-size: 14px; color: #9ca3af;">
                    <tr>
                        <td style="padding: 4px 0;">Cotisation :</td>
                        <td style="text-align: right; color: #fff;">{{ number_format($group->contribution_amount, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">Fréquence :</td>
                        <td style="text-align: right; color: #fff;">{{ ucfirst($group->frequency) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">Démarrage :</td>
                        <td style="text-align: right; color: #fff;">{{ \Carbon\Carbon::parse($group->start_date)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="background-color: #1a1a1a; border-radius: 12px; padding: 15px; margin-bottom: 30px; border: 1px dashed #333;">
            <p style="font-size: 12px; color: #6b7280; margin: 0; text-align: center;">
                🔒 Ce contrat a été ancré sur la Blockchain Polygon.<br>
                Adresse : <span style="color: #10b981; font-family: monospace;">{{ substr($group->contract_address, 0, 10) }}...</span>
            </p>
        </div>

        <div style="text-align: center;">
            <p style="font-size: 14px; color: #9ca3af;">Merci de votre confiance,<br><strong style="color: #d4af37;">L'équipe TontineChain</strong></p>
        </div>

        <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #222; text-align: center;">
            <p style="font-size: 10px; color: #374151;">
                MIABE 2026 — Excellence dans l'Inclusion Financière
            </p>
        </div>
    </div>
</body>
</html>
