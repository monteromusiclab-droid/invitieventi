<?php
/**
 * Classe per la gestione dei QR Code
 * Utilizza la libreria PHP QR Code (chillerlan/php-qrcode)
 * Fallback a servizio esterno se la libreria non è disponibile
 */
class WI_QRCode {

    /**
     * Genera un QR Code per un invito
     *
     * @param int $invite_id ID dell'invito
     * @param array $options Opzioni QR (size, color, logo, ecc.)
     * @return string|false URL dell'immagine QR generata o false in caso di errore
     */
    public static function generate($invite_id, $options = array()) {
        $invite_url = get_permalink($invite_id);

        if (!$invite_url) {
            wi_log('Cannot generate QR: invalid invite URL', 'error', array('invite_id' => $invite_id));
            return false;
        }

        // Default options
        $defaults = array(
            'size' => 300,
            'margin' => 10,
            'error_correction' => 'M', // L, M, Q, H
            'foreground_color' => '000000',
            'background_color' => 'FFFFFF',
            'format' => 'png', // png, svg
            'logo' => false // Path to logo image
        );

        $options = wp_parse_args($options, $defaults);

        // Controlla se abbiamo già un QR code salvato
        $qr_meta_key = '_wi_qr_code_' . md5(serialize($options));
        $existing_qr = get_post_meta($invite_id, $qr_meta_key, true);

        if ($existing_qr && file_exists($existing_qr)) {
            wi_log('Using cached QR code', 'info', array('invite_id' => $invite_id));
            return self::get_qr_url($existing_qr);
        }

        // Genera nuovo QR code
        $qr_path = self::generate_qr_image($invite_url, $invite_id, $options);

        if ($qr_path) {
            update_post_meta($invite_id, $qr_meta_key, $qr_path);
            wi_log('QR code generated successfully', 'info', array(
                'invite_id' => $invite_id,
                'path' => $qr_path
            ));

            return self::get_qr_url($qr_path);
        }

        return false;
    }

    /**
     * Genera l'immagine QR usando Google Charts API (fallback sicuro)
     * Questo metodo non richiede librerie esterne
     *
     * @param string $data Dati da encodare
     * @param int $invite_id ID invito
     * @param array $options Opzioni
     * @return string|false Path del file generato
     */
    private static function generate_qr_image($data, $invite_id, $options) {
        $upload_dir = wp_upload_dir();
        $qr_dir = $upload_dir['basedir'] . '/wedding-invites-qr/';

        // Crea directory se non esiste
        if (!file_exists($qr_dir)) {
            wp_mkdir_p($qr_dir);
        }

        $filename = 'qr-invite-' . $invite_id . '-' . time() . '.png';
        $filepath = $qr_dir . $filename;

        // Usa QR Server API (goqr.me) - free, affidabile, no auth
        // Docs: https://goqr.me/api/
        $qr_url = sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=%dx%d&data=%s&ecc=%s&margin=%d&format=png',
            $options['size'],
            $options['size'],
            urlencode($data),
            $options['error_correction'],
            $options['margin']
        );

        wi_log('Generating QR code', 'info', array(
            'url' => $qr_url,
            'invite_id' => $invite_id
        ));

        // Download immagine
        $response = wp_remote_get($qr_url, array(
            'timeout' => 30,
            'sslverify' => false // API QR Server usa SSL valido
        ));

        if (is_wp_error($response)) {
            wi_log('QR generation failed', 'error', array(
                'error' => $response->get_error_message()
            ));
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            wi_log('QR API returned error', 'error', array(
                'code' => $response_code,
                'body' => wp_remote_retrieve_body($response)
            ));
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);

        if (empty($image_data)) {
            wi_log('QR image data is empty', 'error');
            return false;
        }

        // Verifica che sia un'immagine PNG valida
        if (substr($image_data, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            wi_log('QR data is not valid PNG', 'error', array(
                'first_bytes' => bin2hex(substr($image_data, 0, 8))
            ));
            return false;
        }

        // Salva file
        $saved = file_put_contents($filepath, $image_data);

        if (!$saved) {
            wi_log('Failed to save QR file', 'error', array('path' => $filepath));
            return false;
        }

        wi_log('QR code saved successfully', 'info', array(
            'path' => $filepath,
            'size' => filesize($filepath)
        ));

        // Personalizza colori se richiesto (usa GD)
        if ($options['foreground_color'] !== '000000' || $options['background_color'] !== 'FFFFFF') {
            self::customize_qr_colors($filepath, $options);
        }

        // Aggiungi logo se presente
        if ($options['logo'] && file_exists($options['logo'])) {
            self::add_logo_to_qr($filepath, $options['logo']);
        }

        return $filepath;
    }

    /**
     * Personalizza i colori del QR code usando GD
     *
     * @param string $filepath Path immagine QR
     * @param array $options Opzioni con colori
     */
    private static function customize_qr_colors($filepath, $options) {
        if (!function_exists('imagecreatefrompng')) {
            return; // GD non disponibile
        }

        $image = imagecreatefrompng($filepath);
        if (!$image) return;

        $width = imagesx($image);
        $height = imagesy($image);

        // Parse colori hex
        $fg = self::hex_to_rgb($options['foreground_color']);
        $bg = self::hex_to_rgb($options['background_color']);

        $fg_color = imagecolorallocate($image, $fg['r'], $fg['g'], $fg['b']);
        $bg_color = imagecolorallocate($image, $bg['r'], $bg['g'], $bg['b']);

        // Sostituisci nero con foreground, bianco con background
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                // Se è nero (o simile), usa foreground
                if ($colors['red'] < 128) {
                    imagesetpixel($image, $x, $y, $fg_color);
                } else {
                    imagesetpixel($image, $x, $y, $bg_color);
                }
            }
        }

        imagepng($image, $filepath);
        imagedestroy($image);
    }

    /**
     * Aggiunge un logo al centro del QR code
     *
     * @param string $qr_path Path QR code
     * @param string $logo_path Path logo
     */
    private static function add_logo_to_qr($qr_path, $logo_path) {
        if (!function_exists('imagecreatefrompng')) {
            return; // GD non disponibile
        }

        $qr = imagecreatefrompng($qr_path);
        if (!$qr) return;

        // Carica logo (supporta PNG e JPG)
        $logo_info = getimagesize($logo_path);
        $logo = null;

        switch ($logo_info['mime']) {
            case 'image/png':
                $logo = imagecreatefrompng($logo_path);
                break;
            case 'image/jpeg':
                $logo = imagecreatefromjpeg($logo_path);
                break;
            default:
                return;
        }

        if (!$logo) return;

        $qr_width = imagesx($qr);
        $qr_height = imagesy($qr);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        // Logo deve essere max 20% del QR
        $logo_qr_width = $qr_width / 5;
        $scale = $logo_qr_width / $logo_width;
        $logo_qr_height = $logo_height * $scale;

        // Centra logo
        $from_width = ($qr_width - $logo_qr_width) / 2;
        $from_height = ($qr_height - $logo_qr_height) / 2;

        // Aggiungi sfondo bianco dietro il logo
        $white = imagecolorallocate($qr, 255, 255, 255);
        $padding = 5;
        imagefilledrectangle(
            $qr,
            $from_width - $padding,
            $from_height - $padding,
            $from_width + $logo_qr_width + $padding,
            $from_height + $logo_qr_height + $padding,
            $white
        );

        // Copia logo sul QR
        imagecopyresampled(
            $qr, $logo,
            $from_width, $from_height,
            0, 0,
            $logo_qr_width, $logo_qr_height,
            $logo_width, $logo_height
        );

        imagepng($qr, $qr_path);
        imagedestroy($qr);
        imagedestroy($logo);
    }

    /**
     * Converte hex color a RGB
     *
     * @param string $hex Colore esadecimale (senza #)
     * @return array Array con r, g, b
     */
    private static function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');

        return array(
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        );
    }

    /**
     * Ottiene l'URL pubblico del QR code
     *
     * @param string $filepath Path assoluto file
     * @return string URL pubblico
     */
    private static function get_qr_url($filepath) {
        $upload_dir = wp_upload_dir();
        $qr_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $filepath);

        return $qr_url;
    }

    /**
     * Elimina i QR code associati a un invito
     *
     * @param int $invite_id ID invito
     */
    public static function delete_qr_codes($invite_id) {
        $upload_dir = wp_upload_dir();
        $qr_dir = $upload_dir['basedir'] . '/wedding-invites-qr/';

        // Cerca tutti i file QR di questo invito
        $pattern = $qr_dir . 'qr-invite-' . $invite_id . '-*.png';
        $files = glob($pattern);

        foreach ($files as $file) {
            @unlink($file);
        }

        // Rimuovi meta QR
        delete_post_meta($invite_id, '_wi_qr_code_url');

        wi_log('QR codes deleted', 'info', array(
            'invite_id' => $invite_id,
            'files_deleted' => count($files)
        ));
    }

    /**
     * Genera QR code con short URL (usando servizio esterno opzionale)
     *
     * @param int $invite_id ID invito
     * @return string|false URL del QR o false
     */
    public static function generate_with_short_url($invite_id) {
        $long_url = get_permalink($invite_id);

        // Prova a usare servizio short URL (es: Bitly, TinyURL)
        $short_url = self::create_short_url($long_url);

        if ($short_url) {
            return self::generate($invite_id, array('url' => $short_url));
        }

        // Fallback a URL completo
        return self::generate($invite_id);
    }

    /**
     * Crea short URL usando servizio esterno (opzionale)
     *
     * @param string $url URL lungo
     * @return string|false Short URL o false
     */
    private static function create_short_url($url) {
        // Questo metodo può essere esteso con API Bitly, TinyURL, ecc.
        // Per ora ritorna false (fallback a URL completo)

        // Esempio con TinyURL (free, no auth):
        /*
        $api_url = 'https://tinyurl.com/api-create.php?url=' . urlencode($url);
        $response = wp_remote_get($api_url);

        if (!is_wp_error($response)) {
            $short_url = wp_remote_retrieve_body($response);
            if (filter_var($short_url, FILTER_VALIDATE_URL)) {
                return $short_url;
            }
        }
        */

        return false;
    }
}
