<?php
/**
 * Classe per validazione input centralizzata
 * Fornisce metodi riutilizzabili per validare dati in tutto il plugin
 */

if (!defined('ABSPATH')) exit;

class WI_Validator {

    /**
     * Errori di validazione accumulati
     * @var array
     */
    private static $errors = array();

    /**
     * Valida dati invito completi
     *
     * @param array $data Dati da validare
     * @return bool True se valido, false altrimenti
     */
    public static function validate_invite_data($data) {
        self::$errors = array();

        // Valida titolo
        if (!self::validate_title($data['title'] ?? '')) {
            self::$errors[] = 'Il titolo è obbligatorio e non può superare 255 caratteri';
        }

        // Valida messaggio
        if (!self::validate_message($data['message'] ?? '')) {
            self::$errors[] = 'Il messaggio è obbligatorio';
        }

        // Valida data evento
        if (!self::validate_date($data['event_date'] ?? '')) {
            self::$errors[] = 'Data evento non valida (formato richiesto: YYYY-MM-DD)';
        }

        // Valida ora evento
        if (!self::validate_time($data['event_time'] ?? '')) {
            self::$errors[] = 'Ora evento non valida (formato richiesto: HH:MM)';
        }

        // Valida location
        if (!self::validate_location($data['event_location'] ?? '')) {
            self::$errors[] = 'Il nome del luogo è obbligatorio';
        }

        // Valida indirizzo
        if (!self::validate_address($data['event_address'] ?? '')) {
            self::$errors[] = 'L\'indirizzo completo è obbligatorio';
        }

        // Valida template ID
        if (!self::validate_template_id($data['template_id'] ?? 0)) {
            self::$errors[] = 'Seleziona un template valido';
        }

        return empty(self::$errors);
    }

    /**
     * Valida titolo
     *
     * @param string $title
     * @return bool
     */
    public static function validate_title($title) {
        $title = trim($title);
        return !empty($title) && strlen($title) <= 255;
    }

    /**
     * Valida messaggio
     *
     * @param string $message
     * @return bool
     */
    public static function validate_message($message) {
        return !empty(trim($message));
    }

    /**
     * Valida data (formato YYYY-MM-DD)
     *
     * @param string $date
     * @return bool
     */
    public static function validate_date($date) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        // Verifica che sia una data valida
        list($year, $month, $day) = explode('-', $date);
        return checkdate((int)$month, (int)$day, (int)$year);
    }

    /**
     * Valida ora (formato HH:MM o HH:MM:SS)
     *
     * @param string $time
     * @return bool
     */
    public static function validate_time($time) {
        return preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', $time);
    }

    /**
     * Valida location
     *
     * @param string $location
     * @return bool
     */
    public static function validate_location($location) {
        return !empty(trim($location));
    }

    /**
     * Valida indirizzo
     *
     * @param string $address
     * @return bool
     */
    public static function validate_address($address) {
        return !empty(trim($address)) && strlen(trim($address)) >= 5;
    }

    /**
     * Valida template ID
     *
     * @param int $template_id
     * @return bool
     */
    public static function validate_template_id($template_id) {
        $template_id = intval($template_id);

        if ($template_id <= 0) {
            return false;
        }

        // Verifica che il template esista
        $template = WI_Templates::get_template($template_id);
        return ($template !== null);
    }

    /**
     * Valida email
     *
     * @param string $email
     * @return bool
     */
    public static function validate_email($email) {
        return is_email($email);
    }

    /**
     * Valida URL
     *
     * @param string $url
     * @return bool
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida numero intero con range
     *
     * @param mixed $value Valore da validare
     * @param int $min Valore minimo
     * @param int $max Valore massimo
     * @return bool
     */
    public static function validate_int_range($value, $min, $max) {
        $value = intval($value);
        return $value >= $min && $value <= $max;
    }

    /**
     * Valida numero float con range
     *
     * @param mixed $value Valore da validare
     * @param float $min Valore minimo
     * @param float $max Valore massimo
     * @return bool
     */
    public static function validate_float_range($value, $min, $max) {
        $value = floatval($value);
        return $value >= $min && $value <= $max;
    }

    /**
     * Ottieni errori di validazione
     *
     * @return array
     */
    public static function get_errors() {
        return self::$errors;
    }

    /**
     * Reset errori
     */
    public static function reset_errors() {
        self::$errors = array();
    }

    /**
     * Ottieni messaggio errori formattato per HTML
     *
     * @return string
     */
    public static function get_errors_html() {
        if (empty(self::$errors)) {
            return '';
        }

        $html = '<div class="notice notice-error is-dismissible">';
        $html .= '<p><strong>Errori di validazione:</strong></p>';
        $html .= '<ul>';
        foreach (self::$errors as $error) {
            $html .= '<li>' . esc_html($error) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}
