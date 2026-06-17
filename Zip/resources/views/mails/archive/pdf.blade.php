<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Arsip Surat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; margin-top: 0; color: #555; }
    </style>
</head>
<body>
    <h2>Daftar Arsip Surat</h2>
    <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>No. Referensi</th>
                <th>Tipe</th>
                <th>Perihal</th>
                <th>Pengirim/Penerima</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mails as $index => $mail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $mail->reference_number }}</td>
                <td>{{ $mail->type_label }}</td>
                <td>{{ $mail->subject }}</td>
                <td>{{ $mail->type === 'incoming' ? $mail->sender_name : $mail->recipient_name }}</td>
                <td>{{ $mail->tanggal_surat->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
