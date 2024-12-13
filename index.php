<?php
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    function fetchEmails($site, $domain, $niche) {
        $query = "site:$site \"$niche\" \"$domain\"";
        $url = "https://www.google.com/search?q=" . urlencode($query);
        $emails = [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $response = curl_exec($ch);
        curl_close($ch);

        preg_match_all('/[a-zA-Z0-9._%+-]+@' . preg_quote($domain, '/') . '/', $response, $matches);
        if (isset($matches[0])) {
            $emails = array_unique($matches[0]);
        }

        return $emails;
    }

    function exportToExcel($emails, $fileName = "emails.xlsx") {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'Email Addresses');
    
        foreach ($emails as $index => $email) {
            $sheet->setCellValue('A' . ($index + 2), $email);
        }
    
        $directory = __DIR__ . "/emails";
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    
        $filePath = $directory . "/$fileName";
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $counter = 1;
    
        while (file_exists($filePath)) {
            $filePath = $directory . "/{$fileNameWithoutExt}-{$counter}.$extension";
            $counter++;
        }
    
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        return $filePath;
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $site = $data['site'] ?? '';
        $domain = $data['domain'] ?? '';
        $niche = $data['niche'] ?? '';

        if ($site && $domain && $niche) {
            $emails = fetchEmails($site, $domain, $niche);

            if (!empty($emails)) {
                $file = exportToExcel($emails);
                echo json_encode(['success' => true, 'file' => $file]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No emails found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        }
        exit;
    }
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Extractor Bot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f9f9;
            color: #333;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            color: #09186d;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            color: #006c67;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #006c67;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #004d47;
        }

        #status {
            margin-top: 20px;
            font-size: 16px;
        }

        a {
            color: #09186d;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Extractor</h1>
        <form id="emailExtractorForm">
            <label for="site">Website (e.g., facebook.com):</label>
            <input type="text" id="site" name="site" required>

            <label for="domain">Email Domain (e.g., gmail.com):</label>
            <input type="text" id="domain" name="domain" required>

            <label for="niche">Niche (e.g., marketing, engineering):</label>
            <input type="text" id="niche" name="niche" required>

            <button type="submit">Extract Emails</button>
        </form>

        <p id="status"></p>
    </div>

    <script>
        document.getElementById('emailExtractorForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const site = document.getElementById('site').value;
            const domain = document.getElementById('domain').value;
            const niche = document.getElementById('niche').value;

            document.getElementById('status').innerText = 'Processing...';

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ site, domain, niche })
                });

                const result = await response.json();
                if (result.success) {
                    document.getElementById('status').innerHTML = `<p>Emails successfully extracted! <a href="${result.file}" download>Download Emails</a></p>`;
                } else {
                    document.getElementById('status').innerText = result.message || 'Failed to extract emails.';
                }
            } catch (error) {
                document.getElementById('status').innerText = 'An error occurred: ' + error.message;
            }
        });
    </script>
</body>
</html>
