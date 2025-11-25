<?php
$preview_title = "";
$preview_content = "";
$message = "";

if (isset($_REQUEST['url'])) {
    $url = $_REQUEST['url'];

    /* --- INIZIO CONTROMISURA (WHITELIST ESTERNA) ---*/

    // estrazione host da url
    $host_richiesto = parse_url($url, PHP_URL_HOST);

    $file_whitelist = __DIR__ . '/whitelist.php';
    if (file_exists($file_whitelist)) {
        $domini_fidati = include $file_whitelist;
    } else {
        $domini_fidati = []; 
    }

    // $host_richiesto è false/null o l'host non è presente nella whitelist
    if (!$host_richiesto || !in_array($host_richiesto, $domini_fidati, true)) {
        http_response_code(403); // Codice HTTP Forbidden
        
        // htmlspecialchars sanifica input per XSS
        die("<div style='color: darkred; background-color: #ffdddd; padding: 15px; border: 1px solid red; border-radius: 5px; margin-top: 20px;'>
                <strong>⛔ ACCESSO NEGATO </strong><br>
                L'host richiesto (<code>" . htmlspecialchars($host_richiesto) . "</code>) non è presente nel file di configurazione dei siti autorizzati.
             </div>");
    }

// --- FINE CONTROMISURA ---*/
    
    // Tenta di recuperare i primi 1000 bytes dell'URL
    $content = @file_get_contents($url, length:1000);

    if ($content === FALSE) {
        $message = "<div style='color: red;'>Errore: Impossibile raggiungere l'URL o accesso negato.</div>";
    } else {
        // Simulazione anteprima (titolo)
        if (preg_match("/<title>(.*)<\/title>/siU", $content, $title_matches)) {
            $preview_title = $title_matches[1];
        } else {
            $preview_title = "Nessun titolo trovato (forse non è HTML?)";
        }

        // Simulazione anteprima (contenuto)
        $preview_content = htmlspecialchars(substr($content, 0));
        $message = "<div style='color: green;'>Anteprima generata con successo!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Link Preview Generator (Vulnerable)</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; background-color: #f4f4f4; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input[type="text"] { width: 80%; padding: 10px; margin-bottom: 10px; }
        button { padding: 10px 20px; cursor: pointer; background: #007bff; color: white; border: none; }
        .preview-box { border: 1px solid #ddd; padding: 15px; margin-top: 20px; background: #fafafa; }
        code { display: block; background: #333; color: #0f0; padding: 10px; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>

<div class="container">
    <h2>Generatore di Anteprima Link</h2>
    <p>Inserisci un URL per generare un'anteprima (Titolo ed estratto).</p>
    
    <form method="POST">
        <input type="text" name="url" placeholder="es. https://example.com" required>
        <button type="submit">Genera Anteprima</button>
    </form>

    <?= $message ?>

    <?php if ($preview_title || $preview_content): ?>
    <div class="preview-box">
        <h3>Risultato Anteprima:</h3>
        <p><strong>Titolo Pagina:</strong> <?= $preview_title ?></p>
        
        <hr>
        
        <p><strong>Anteprima Contenuto (Primi 1000 bytes):</strong></p>
        <code><?= $preview_content ?>...</code>
    </div>
    <?php endif; ?>
</div>

</body>
</html>