{**
 * templates/pdfView.tpl
 * Cangkang utama untuk dokumen PDF LoA (Versi Sederhana).
 *}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Letter of Acceptance</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { margin-top: 20px; }
        .footer { margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        {$headerHtml nofilter}
    </div>

    <p><strong>Date:</strong> {$acceptanceDate}</p>

    <p>
        <strong>To:</strong><br>
        {$authorNamesString}
    </p>

    <div class="content">
        {$bodyHtml nofilter}
    </div>

    <div class="footer">
        {$footerHtml nofilter}
    </div>
</body>
</html>