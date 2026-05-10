<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrat Officiel - {{ $group->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; padding: 20px; color: #333; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .header-box { background: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .meta-data { margin-bottom: 5px; }
        .hash-box { background: #e8f4fd; border-left: 4px solid #3498db; padding: 10px; font-family: monospace; font-size: 12px; margin-bottom: 20px; word-break: break-all; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #3498db; color: white; }
        .footer { margin-top: 40px; font-size: 12px; color: #7f8c8d; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <h1>Contrat de Tontine Numérique</h1>
    
    <div class="header-box">
        <div class="meta-data"><strong>Nom du Groupe :</strong> {{ $group->name }}</div>
        <div class="meta-data"><strong>Montant de la Cotisation :</strong> {{ number_format($group->contribution_amount, 0, ',', ' ') }} FCFA</div>
        <div class="meta-data"><strong>Fréquence :</strong> {{ ucfirst($group->frequency) }}</div>
        <div class="meta-data"><strong>Méthode de Payout :</strong> {{ ucfirst($group->payout_method) }}</div>
        <div class="meta-data"><strong>Date de démarrage officielle :</strong> {{ \Carbon\Carbon::parse($group->start_date)->format('d/m/Y') }}</div>
        <div class="meta-data"><strong>Créateur du Groupe :</strong> {{ $group->creator->full_name ?? 'Inconnu' }} ({{ $group->creator->phone }})</div>
    </div>

    <h3>Preuve Blockchain (Immuabilité)</h3>
    <p style="font-size: 14px;">Les termes de ce contrat et les engagements des membres ont été scellés sur le réseau Polygon.</p>
    <div class="hash-box">
        <strong>Contract Address :</strong> {{ $group->contract_address }}<br>
        <strong>Transaction Hash :</strong> {{ $group->contract_tx_hash }}
    </div>

    <h3>Membres Engagés</h3>
    <table>
        <thead>
            <tr>
                <th>Position</th>
                <th>Nom Complet</th>
                <th>Téléphone</th>
                <th>Score de Confiance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group->members->sortBy('position') as $member)
            <tr>
                <td>{{ $member->position }}</td>
                <td>{{ $member->user->full_name ?? 'N/A' }}</td>
                <td>{{ $member->user->phone }}</td>
                <td>{{ $member->user->score_confiance }}/100</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement par la plateforme TontineChain le {{ now()->format('d/m/Y H:i:s') }}.<br>
        Ce document constitue une preuve d'engagement mutuel entre les membres du groupe.
    </div>

</body>
</html>
