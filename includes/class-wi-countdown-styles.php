<?php
if (!defined('ABSPATH')) exit;

class WI_Countdown_Styles {
    
    public static function get_countdown_styles() {
        return array(
            1 => array(
                'name' => 'Classico',
                'description' => 'Stile elegante e tradizionale',
                'css' => self::get_style_1_css(),
                'html_template' => self::get_style_1_html()
            ),
            2 => array(
                'name' => 'Moderno',
                'description' => 'Design contemporaneo e minimalista',
                'css' => self::get_style_2_css(),
                'html_template' => self::get_style_2_html()
            ),
            3 => array(
                'name' => 'Lussurioso',
                'description' => 'Oro e nero elegante',
                'css' => self::get_style_3_css(),
                'html_template' => self::get_style_3_html()
            ),
            4 => array(
                'name' => 'Floreale',
                'description' => 'Con decorazioni floreali',
                'css' => self::get_style_4_css(),
                'html_template' => self::get_style_4_html()
            ),
            5 => array(
                'name' => 'Rotondo',
                'description' => 'Cifre circolari e arrotondate',
                'css' => self::get_style_5_css(),
                'html_template' => self::get_style_5_html()
            ),
            6 => array(
                'name' => 'Gradiente',
                'description' => 'Con effetti sfumati',
                'css' => self::get_style_6_css(),
                'html_template' => self::get_style_6_html()
            ),
            7 => array(
                'name' => 'Neon',
                'description' => 'Stile neon colorato',
                'css' => self::get_style_7_css(),
                'html_template' => self::get_style_7_html()
            ),
            8 => array(
                'name' => 'Vintage',
                'description' => 'Effetto retro-vintage',
                'css' => self::get_style_8_css(),
                'html_template' => self::get_style_8_html()
            ),
            9 => array(
                'name' => 'Geometrico',
                'description' => 'Con forme geometriche',
                'css' => self::get_style_9_css(),
                'html_template' => self::get_style_9_html()
            ),
            10 => array(
                'name' => 'Cielo',
                'description' => 'Con gradiente cielo',
                'css' => self::get_style_10_css(),
                'html_template' => self::get_style_10_html()
            ),
            11 => array(
                'name' => 'Oceano',
                'description' => 'Blu oceano profondo',
                'css' => self::get_style_11_css(),
                'html_template' => self::get_style_11_html()
            ),
            12 => array(
                'name' => 'Tramonto',
                'description' => 'Colori caldi del tramonto',
                'css' => self::get_style_12_css(),
                'html_template' => self::get_style_12_html()
            ),
            13 => array(
                'name' => 'Cristallo',
                'description' => 'Effetto cristallo trasparente',
                'css' => self::get_style_13_css(),
                'html_template' => self::get_style_13_html()
            ),
            14 => array(
                'name' => 'Ombra',
                'description' => 'Con effetti ombra 3D',
                'css' => self::get_style_14_css(),
                'html_template' => self::get_style_14_html()
            ),
            15 => array(
                'name' => 'Animato',
                'description' => 'Fortemente animato e dinamico',
                'css' => self::get_style_15_css(),
                'html_template' => self::get_style_15_html()
            )
        );
    }
    
    private static function get_style_1_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 30px;
    background: #f8f9fa;
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 48px;
    font-weight: bold;
    color: #6366f1;
    line-height: 1;
    margin-bottom: 10px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 14px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 2px;
}
        ';
    }
    
    private static function get_style_1_html() {
        return '
<div class="countdown-item">
    <div class="countdown-value">${days}</div>
    <div class="countdown-label">Giorni</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${hours}</div>
    <div class="countdown-label">Ore</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${minutes}</div>
    <div class="countdown-label">Minuti</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${seconds}</div>
    <div class="countdown-label">Secondi</div>
</div>
        ';
    }
    
    private static function get_style_2_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 20px;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 15px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 42px;
    font-weight: bold;
    color: #ffffff;
    line-height: 1;
    margin-bottom: 8px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #cbd5e1;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_2_html() {
        return '
<div class="countdown-item">
    <div class="countdown-value">${days}</div>
    <div class="countdown-label">G</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${hours}</div>
    <div class="countdown-label">H</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${minutes}</div>
    <div class="countdown-label">M</div>
</div>
<div class="countdown-item">
    <div class="countdown-value">${seconds}</div>
    <div class="countdown-label">S</div>
</div>
        ';
    }
    
    private static function get_style_3_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 30px;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    padding: 20px;
    background: rgba(212,175,55,0.1);
    border: 2px solid #d4af37;
    border-radius: 12px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 48px;
    font-weight: bold;
    color: #d4af37;
    line-height: 1;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 14px;
    color: #d4af37;
    text-transform: uppercase;
    letter-spacing: 2px;
}
        ';
    }
    
    private static function get_style_3_html() {
        return self::get_style_1_html();
    }
    
    private static function get_style_4_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 25px;
    padding: 30px;
    background: url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Cpath d=%27M20,80 Q50,20 80,80%27 stroke=%27%23f0abfc%27 fill=%27none%27 stroke-width=%272%27/%3E%3C/svg%3E");
    background-color: #fff5f9;
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    position: relative;
    flex-shrink: 0;
}

.countdown-item::before {
    content: "✿";
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    color: #ec4899;
    font-size: 20px;
}

.countdown-value {
    font-size: 46px;
    font-weight: bold;
    color: #be185d;
    line-height: 1;
    margin-bottom: 10px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 14px;
    color: #be185d;
    text-transform: uppercase;
    letter-spacing: 2px;
}
        ';
    }
    
    private static function get_style_4_html() {
        return self::get_style_1_html();
    }
    
    private static function get_style_5_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 25px;
    background: #f0f9ff;
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 130px;
    min-width: 130px;
    padding: 25px;
    background: #ffffff;
    border-radius: 50%;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 3px solid #0284c7;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 40px;
    font-weight: bold;
    color: #0284c7;
    line-height: 1;
    margin-bottom: 5px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 11px;
    color: #0284c7;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_5_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_6_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: rgba(255,255,255,0.15);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.countdown-value {
    font-size: 44px;
    font-weight: bold;
    color: #ffffff;
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_6_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_7_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 30px;
    background: #0a0e27;
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    padding: 15px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 50px;
    font-weight: bold;
    color: #0ff;
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 0 10px #0ff, 0 0 20px #0ff, 0 0 30px #0f0;
    font-family: "Courier New", monospace;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #0ff;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-shadow: 0 0 5px #0ff;
}
        ';
    }
    
    private static function get_style_7_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_8_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 30px;
    background: linear-gradient(135deg, #8b7355 0%, #a68a64 100%);
    position: relative;
}

.wi-countdown::before {
    content: "";
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        90deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,0.03) 2px,
        rgba(0,0,0,0.03) 4px
    );
    pointer-events: none;
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    padding: 20px;
    background: rgba(139,115,85,0.5);
    border: 2px solid #c9b8a3;
    border-radius: 5px;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 44px;
    font-weight: bold;
    color: #f5e6d3;
    line-height: 1;
    margin-bottom: 10px;
    font-family: "Georgia", serif;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 13px;
    color: #f5e6d3;
    text-transform: uppercase;
    letter-spacing: 2px;
}
        ';
    }
    
    private static function get_style_8_html() {
        return self::get_style_1_html();
    }
    
    private static function get_style_9_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 30px;
    background: linear-gradient(45deg, #f3f3f3 25%, transparent 25%, transparent 75%, #f3f3f3 75%, #f3f3f3),
                linear-gradient(45deg, #f3f3f3 25%, transparent 25%, transparent 75%, #f3f3f3 75%, #f3f3f3);
    background-size: 40px 40px;
    background-position: 0 0, 20px 20px;
    background-color: #ffffff;
}

.countdown-item {
    text-align: center;
    flex: 1;
    min-width: 120px;
    padding: 20px;
    background: #ffffff;
    clip-path: polygon(0% 15%, 15% 0%, 100% 0%, 85% 15%, 100% 85%, 85% 100%, 15% 100%, 0% 85%);
    border: 2px solid #10b981;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 44px;
    font-weight: bold;
    color: #10b981;
    line-height: 1;
    margin-bottom: 10px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #10b981;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_9_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_10_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 30px;
    background: linear-gradient(180deg, #87ceeb 0%, #e0f6ff 50%, #ffffff 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: rgba(255,255,255,0.7);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.countdown-value {
    font-size: 42px;
    font-weight: bold;
    color: #0369a1;
    line-height: 1;
    margin-bottom: 8px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #0369a1;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_10_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_11_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 30px;
    background: linear-gradient(135deg, #0c4a6e 0%, #164e63 50%, #083344 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: rgba(3,102,214,0.2);
    border: 2px solid #0ea5e9;
    border-radius: 12px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 42px;
    font-weight: bold;
    color: #7dd3fc;
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 0 10px rgba(125,211,252,0.5);
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #7dd3fc;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_11_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_12_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 30px;
    background: linear-gradient(135deg, #7c2d12 0%, #ea580c 50%, #fed7aa 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.countdown-value {
    font-size: 42px;
    font-weight: bold;
    color: #ffffff;
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #fed7aa;
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_12_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_13_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 30px;
    background: transparent;
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.countdown-value {
    font-size: 42px;
    font-weight: bold;
    color: rgba(255,255,255,0.9);
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 0 20px rgba(255,255,255,0.3);
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    letter-spacing: 1px;
}
        ';
    }
    
    private static function get_style_13_html() {
        return self::get_style_2_html();
    }
    
    private static function get_style_14_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 30px;
    background: #f8f9fa;
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 120px;
    min-width: 120px;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: -5px 5px 15px rgba(0,0,0,0.2),
                -10px 10px 25px rgba(0,0,0,0.1),
                inset 1px 1px 0 rgba(255,255,255,0.5);
    flex-shrink: 0;
}

.countdown-value {
    font-size: 44px;
    font-weight: bold;
    color: #1e293b;
    line-height: 1;
    margin-bottom: 10px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    width: 100%;
    text-align: center;
    display: block;
}
        ';
    }
    
    private static function get_style_14_html() {
        return self::get_style_1_html();
    }
    
    private static function get_style_15_css() {
        return '
.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.countdown-item {
    text-align: center;
    flex: 1;
    max-width: 110px;
    min-width: 110px;
    padding: 18px;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    flex-shrink: 0;
}

.countdown-value {
    font-size: 40px;
    font-weight: bold;
    color: #ffffff;
    line-height: 1;
    margin-bottom: 6px;
    font-variant-numeric: tabular-nums;
    width: 100%;
    text-align: center;
    display: block;
}

.countdown-label {
    font-size: 11px;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
    width: 100%;
    text-align: center;
    display: block;
}
        ';
    }
    
    private static function get_style_15_html() {
        return self::get_style_2_html();
    }
}
