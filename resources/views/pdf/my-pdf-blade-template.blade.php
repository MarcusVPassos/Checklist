<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Checklist {{ $model->placa }}</title>
    <style>
        /* CSS simples; prefira fontes padrão do gerador de PDF (ex.: DejaVu Sans) */
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1   { font-size: 18px; margin: 0 0 10px }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px }
        .box  { border: 1px solid #000; padding: 8px; border-radius: 4px }
    </style>
</head>
<body>
    <h1>Checklist / Registro #{{ $model->id }}</h1>

    <div class="grid">
        <div class="box">
            <strong>Placa:</strong> {{ $model->placa }}<br>
            <strong>Tipo:</strong> {{ strtoupper($model->tipo) }}<br>
            <strong>Modelo:</strong> {{ $model->modelo }}<br>
            <strong>Marca:</strong> {{ optional($model->marca)->nome }}
        </div>

        <div class="box">
            <strong>Reboque — Condutor:</strong> {{ $model->reboque_condutor }}<br>
            <strong>Reboque — Placa:</strong> {{ $model->reboque_placa }}<br>
            <strong>No pátio:</strong> {{ $model->no_patio ? 'Sim' : 'Não' }}
        </div>
    </div>

    <div class="box" style="margin-top:12px">
        <strong>Observações:</strong><br>
        {{ $model->observacao ?: '—' }}
    </div>

    <p style="margin-top:18px">
        <em>A assinatura será aplicada automaticamente nas coordenadas definidas em
        <code>SignaturePosition</code> quando o PDF for gerado.</em>
    </p>
</body>
</html>
