<?php
/**
 * Visitfy Admin – Content Editor
 */
require dirname(__DIR__) . '/bootstrap.php';
admin_require_login();

$notice = '';
$error = '';
$csrf = admin_csrf_token();
$contentPath = admin_content_config_path();
$content = admin_read_json($contentPath, []);

function he(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_validate_csrf($_POST['csrf'] ?? null)) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_content') {
        /* ── Mockup file uploads ─────────────────────────────── */
        $mockupDir = admin_absolute_path('assets/img/mockups');
        if (!is_dir($mockupDir)) @mkdir($mockupDir, 0775, true);
        $mockupAllowedExt = ['png', 'jpg', 'jpeg', 'webp', 'avif'];
        foreach (['desktop', 'tablet', 'phone'] as $mk) {
            $fileKey = 'mockup_' . $mk . '_file';
            if (isset($_FILES[$fileKey]) && is_array($_FILES[$fileKey]) &&
                ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK &&
                ($_FILES[$fileKey]['tmp_name'] ?? '') !== '') {
                $origName = basename((string)($_FILES[$fileKey]['name'] ?? ''));
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (in_array($ext, $mockupAllowedExt, true)) {
                    $destName = 'mockup-' . $mk . '.' . $ext;
                    $destAbs = $mockupDir . '/' . $destName;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $destAbs)) {
                        $_POST['mockup_' . $mk . '_img'] = 'assets/img/mockups/' . $destName;
                    }
                }
            }
        }

        /* ── Team photo uploads ──────────────────────────────── */
        $teamDir = admin_absolute_path('assets/img/team');
        if (!is_dir($teamDir)) @mkdir($teamDir, 0775, true);
        foreach (['kristian', 'jason'] as $member) {
            $fileKey = 'team_' . $member . '_photo_file';
            if (isset($_FILES[$fileKey]) && is_array($_FILES[$fileKey]) &&
                ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK &&
                ($_FILES[$fileKey]['tmp_name'] ?? '') !== '') {
                $origName = basename((string)($_FILES[$fileKey]['name'] ?? ''));
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'avif'], true)) {
                    $destName = $member . '.' . $ext;
                    $destAbs = $teamDir . '/' . $destName;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $destAbs)) {
                        $_POST['team_' . $member . '_photo'] = 'assets/img/team/' . $destName;
                    }
                }
            }
        }

        /* ── Text arrays ─────────────────────────────────────── */
        $features = preg_split('/\r\n|\r|\n/', (string)($_POST['about_features'] ?? '')) ?: [];
        $features = array_values(array_filter(array_map(fn($v) => trim((string)$v), $features), fn($v) => $v !== ''));

        $perfection = preg_split('/\r\n|\r|\n/', (string)($_POST['about_perfection_points'] ?? '')) ?: [];
        $perfection = array_values(array_filter(array_map(fn($v) => trim((string)$v), $perfection), fn($v) => $v !== ''));

        $heroWords = preg_split('/\r\n|\r|\n/', (string)($_POST['hero_rotating_words'] ?? '')) ?: [];
        $heroWords = array_values(array_filter(array_map(fn($v) => trim((string)$v), $heroWords), fn($v) => $v !== ''));

        /* ── Button FX ────────────────────────────────────────── */
        $allowedButtonFxTargets = ['kontakt_submit', 'partner_submit', 'hero_primary', 'hero_secondary', 'cta_primary', 'cta_secondary'];
        $buttonFxTargetsInput = is_array($_POST['button_fx_targets'] ?? null) ? $_POST['button_fx_targets'] : [];
        $buttonFxTargets = [];
        foreach ($buttonFxTargetsInput as $target) {
            $target = trim((string)$target);
            if ($target !== '' && in_array($target, $allowedButtonFxTargets, true) && !in_array($target, $buttonFxTargets, true)) {
                $buttonFxTargets[] = $target;
            }
        }
        $buttonFxColorRaw = trim((string)($_POST['button_fx_color'] ?? '#8ec9ff'));
        $buttonFxColor = preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $buttonFxColorRaw) ? $buttonFxColorRaw : '#8ec9ff';

        /* ── KPI items ────────────────────────────────────────── */
        $kpiTargets = is_array($_POST['kpi_target'] ?? null) ? $_POST['kpi_target'] : [];
        $kpiSuffixes = is_array($_POST['kpi_suffix'] ?? null) ? $_POST['kpi_suffix'] : [];
        $kpiLabels = is_array($_POST['kpi_label'] ?? null) ? $_POST['kpi_label'] : [];
        $kpiItems = [];
        $kpiCount2 = max(count($kpiTargets), count($kpiSuffixes), count($kpiLabels));
        for ($i = 0; $i < $kpiCount2; $i++) {
            $t = trim((string)($kpiTargets[$i] ?? ''));
            $s = trim((string)($kpiSuffixes[$i] ?? ''));
            $l = trim((string)($kpiLabels[$i] ?? ''));
            if ($t === '' && $s === '' && $l === '') continue;
            $kpiItems[] = ['target' => $t, 'suffix' => $s, 'label' => $l];
        }

        /* ── Vergleich ────────────────────────────────────────── */
        $vergleichNeg = is_array($_POST['vergleich_neg_item'] ?? null) ? $_POST['vergleich_neg_item'] : [];
        $vergleichNeg = array_values(array_filter(array_map(fn($v) => trim((string)$v), $vergleichNeg), fn($v) => $v !== ''));
        $vergleichPos = is_array($_POST['vergleich_pos_item'] ?? null) ? $_POST['vergleich_pos_item'] : [];
        $vergleichPos = array_values(array_filter(array_map(fn($v) => trim((string)$v), $vergleichPos), fn($v) => $v !== ''));

        /* ── Warum 360° cards ─────────────────────────────────── */
        $w360Emojis = is_array($_POST['warum360_emoji'] ?? null) ? $_POST['warum360_emoji'] : [];
        $w360Titles = is_array($_POST['warum360_card_title'] ?? null) ? $_POST['warum360_card_title'] : [];
        $w360Texts  = is_array($_POST['warum360_card_text'] ?? null) ? $_POST['warum360_card_text'] : [];
        $w360Cards = [];
        $w360Count = max(count($w360Emojis), count($w360Titles), count($w360Texts));
        for ($i = 0; $i < $w360Count; $i++) {
            $e = trim((string)($w360Emojis[$i] ?? ''));
            $t = trim((string)($w360Titles[$i] ?? ''));
            $x = trim((string)($w360Texts[$i] ?? ''));
            if ($e === '' && $t === '' && $x === '') continue;
            $w360Cards[] = ['emoji' => $e, 'title' => $t, 'text' => $x];
        }

        /* ── Cases ────────────────────────────────────────────── */
        $caseTags   = is_array($_POST['case_tag'] ?? null) ? $_POST['case_tag'] : [];
        $caseTitles = is_array($_POST['case_title'] ?? null) ? $_POST['case_title'] : [];
        $caseDescs  = is_array($_POST['case_desc'] ?? null) ? $_POST['case_desc'] : [];
        $caseStat1V = is_array($_POST['case_stat1_value'] ?? null) ? $_POST['case_stat1_value'] : [];
        $caseStat1L = is_array($_POST['case_stat1_label'] ?? null) ? $_POST['case_stat1_label'] : [];
        $caseStat2V = is_array($_POST['case_stat2_value'] ?? null) ? $_POST['case_stat2_value'] : [];
        $caseStat2L = is_array($_POST['case_stat2_label'] ?? null) ? $_POST['case_stat2_label'] : [];
        $caseItems = [];
        $caseCount = max(count($caseTags), count($caseTitles), count($caseDescs));
        for ($i = 0; $i < $caseCount; $i++) {
            $tag = trim((string)($caseTags[$i] ?? ''));
            $ttl = trim((string)($caseTitles[$i] ?? ''));
            $dsc = trim((string)($caseDescs[$i] ?? ''));
            if ($tag === '' && $ttl === '' && $dsc === '') continue;
            $caseItems[] = [
                'tag' => $tag, 'title' => $ttl, 'desc' => $dsc,
                'stat1_value' => trim((string)($caseStat1V[$i] ?? '')),
                'stat1_label' => trim((string)($caseStat1L[$i] ?? '')),
                'stat2_value' => trim((string)($caseStat2V[$i] ?? '')),
                'stat2_label' => trim((string)($caseStat2L[$i] ?? '')),
            ];
        }

        /* ── Testimonials ─────────────────────────────────────── */
        $testiTexts     = is_array($_POST['testi_text'] ?? null) ? $_POST['testi_text'] : [];
        $testiAuthors   = is_array($_POST['testi_author'] ?? null) ? $_POST['testi_author'] : [];
        $testiCompanies = is_array($_POST['testi_company'] ?? null) ? $_POST['testi_company'] : [];
        $testiItems = [];
        $testiCount = max(count($testiTexts), count($testiAuthors), count($testiCompanies));
        for ($i = 0; $i < $testiCount; $i++) {
            $t = trim((string)($testiTexts[$i] ?? ''));
            $a = trim((string)($testiAuthors[$i] ?? ''));
            $c = trim((string)($testiCompanies[$i] ?? ''));
            if ($t === '' && $a === '' && $c === '') continue;
            $testiItems[] = ['text' => $t, 'author' => $a, 'company' => $c];
        }

        /* ── Ablauf steps ─────────────────────────────────────── */
        $ablaufEmojis = is_array($_POST['ablauf_emoji'] ?? null) ? $_POST['ablauf_emoji'] : [];
        $ablaufTitles = is_array($_POST['ablauf_step_title'] ?? null) ? $_POST['ablauf_step_title'] : [];
        $ablaufTexts  = is_array($_POST['ablauf_step_text'] ?? null) ? $_POST['ablauf_step_text'] : [];
        $ablaufSteps = [];
        $ablaufCount = max(count($ablaufEmojis), count($ablaufTitles), count($ablaufTexts));
        for ($i = 0; $i < $ablaufCount; $i++) {
            $e = trim((string)($ablaufEmojis[$i] ?? ''));
            $t = trim((string)($ablaufTitles[$i] ?? ''));
            $x = trim((string)($ablaufTexts[$i] ?? ''));
            if ($e === '' && $t === '' && $x === '') continue;
            $ablaufSteps[] = ['emoji' => $e, 'title' => $t, 'text' => $x];
        }

        /* ── FAQ items ────────────────────────────────────────── */
        $faqQuestions = is_array($_POST['faq_question'] ?? null) ? $_POST['faq_question'] : [];
        $faqAnswers   = is_array($_POST['faq_answer'] ?? null) ? $_POST['faq_answer'] : [];
        $faqItems = [];
        $faqCount = max(count($faqQuestions), count($faqAnswers));
        for ($i = 0; $i < $faqCount; $i++) {
            $q = trim((string)($faqQuestions[$i] ?? ''));
            $a = trim((string)($faqAnswers[$i] ?? ''));
            if ($q === '' && $a === '') continue;
            $faqItems[] = ['question' => $q, 'answer' => $a];
        }

        /* ── Build content array ──────────────────────────────── */
        $newContent = [
            'seo' => [
                'home_title' => (string)($_POST['seo_home_title'] ?? ''),
                'home_desc' => (string)($_POST['seo_home_desc'] ?? ''),
            ],
            'intro' => [
                'tagline' => (string)($_POST['intro_tagline'] ?? ''),
                'hint' => (string)($_POST['intro_hint'] ?? ''),
                'skip_button' => (string)($_POST['intro_skip_button'] ?? ''),
            ],
            'hero' => [
                'eyebrow' => (string)($_POST['hero_eyebrow'] ?? ''),
                'prefix' => (string)($_POST['hero_prefix'] ?? ''),
                'rotating_words' => $heroWords,
                'desc' => (string)($_POST['hero_desc'] ?? ''),
                'button_primary_text' => (string)($_POST['hero_btn1_text'] ?? ''),
                'button_primary_link' => (string)($_POST['hero_btn1_link'] ?? ''),
                'button_secondary_text' => (string)($_POST['hero_btn2_text'] ?? ''),
                'button_secondary_link' => (string)($_POST['hero_btn2_link'] ?? ''),
            ],
            'kpi' => [
                'eyebrow' => (string)($_POST['kpi_eyebrow'] ?? ''),
                'title' => (string)($_POST['kpi_title'] ?? ''),
                'items' => $kpiItems,
            ],
            'mockup' => [
                'desktop_img' => (string)($_POST['mockup_desktop_img'] ?? ''),
                'tablet_img' => (string)($_POST['mockup_tablet_img'] ?? ''),
                'phone_img' => (string)($_POST['mockup_phone_img'] ?? ''),
            ],
            'marquee' => [
                'label' => (string)($_POST['marquee_label'] ?? ''),
            ],
            'about' => [
                'eyebrow' => (string)($_POST['about_eyebrow'] ?? ''),
                'title' => (string)($_POST['about_title'] ?? ''),
                'paragraph_1' => (string)($_POST['about_p1'] ?? ''),
                'paragraph_2' => (string)($_POST['about_p2'] ?? ''),
                'paragraph_3' => (string)($_POST['about_p3'] ?? ''),
                'panel_badge' => (string)($_POST['about_panel_badge'] ?? ''),
                'difference_title' => (string)($_POST['about_difference_title'] ?? ''),
                'features_title' => (string)($_POST['about_features_title'] ?? ''),
                'features' => $features,
                'perfection_title' => (string)($_POST['about_perfection_title'] ?? ''),
                'perfection_points' => $perfection,
            ],
            'team' => [
                'eyebrow' => (string)($_POST['team_eyebrow'] ?? ''),
                'title' => (string)($_POST['team_title'] ?? ''),
                'kristian_name' => (string)($_POST['team_kristian_name'] ?? ''),
                'kristian_role' => (string)($_POST['team_kristian_role'] ?? ''),
                'kristian_text' => (string)($_POST['team_kristian_text'] ?? ''),
                'kristian_photo' => (string)($_POST['team_kristian_photo'] ?? ''),
                'jason_name' => (string)($_POST['team_jason_name'] ?? ''),
                'jason_role' => (string)($_POST['team_jason_role'] ?? ''),
                'jason_text' => (string)($_POST['team_jason_text'] ?? ''),
                'jason_photo' => (string)($_POST['team_jason_photo'] ?? ''),
                'jason_link_text' => (string)($_POST['team_jason_link_text'] ?? ''),
                'jason_link_url' => (string)($_POST['team_jason_link_url'] ?? ''),
            ],
            'final_cta' => [
                'title' => (string)($_POST['cta_title'] ?? ''),
                'text' => (string)($_POST['cta_text'] ?? ''),
                'button_primary_text' => (string)($_POST['cta_btn1_text'] ?? ''),
                'button_primary_link' => (string)($_POST['cta_btn1_link'] ?? ''),
                'button_secondary_text' => (string)($_POST['cta_btn2_text'] ?? ''),
                'button_secondary_link' => (string)($_POST['cta_btn2_link'] ?? ''),
            ],
            'footer' => [
                'brand_text' => (string)($_POST['footer_brand_text'] ?? ''),
                'contact_email' => (string)($_POST['footer_contact_email'] ?? ''),
                'website_by_prefix' => (string)($_POST['footer_website_by_prefix'] ?? ''),
                'website_by_name' => (string)($_POST['footer_website_by_name'] ?? ''),
                'website_by_url' => (string)($_POST['footer_website_by_url'] ?? ''),
            ],
            'button_fx' => [
                'enabled' => !empty($_POST['button_fx_enabled']),
                'color' => $buttonFxColor,
                'shimmer' => !empty($_POST['button_fx_shimmer']),
                'targets' => $buttonFxTargets,
            ],
            'mockup_text' => [
                'eyebrow' => (string)($_POST['mockup_text_eyebrow'] ?? ''),
                'title' => (string)($_POST['mockup_text_title'] ?? ''),
                'sub' => (string)($_POST['mockup_text_sub'] ?? ''),
            ],
            'vergleich' => [
                'eyebrow' => (string)($_POST['vergleich_eyebrow'] ?? ''),
                'title' => (string)($_POST['vergleich_title'] ?? ''),
                'sub' => (string)($_POST['vergleich_sub'] ?? ''),
                'badge_negative' => (string)($_POST['vergleich_badge_negative'] ?? ''),
                'badge_positive' => (string)($_POST['vergleich_badge_positive'] ?? ''),
                'negative_items' => $vergleichNeg,
                'positive_items' => $vergleichPos,
            ],
            'warum360' => [
                'eyebrow' => (string)($_POST['warum360_eyebrow'] ?? ''),
                'title' => (string)($_POST['warum360_title_text'] ?? ''),
                'sub' => (string)($_POST['warum360_sub'] ?? ''),
                'cards' => $w360Cards,
            ],
            'tours_text' => [
                'eyebrow' => (string)($_POST['tours_text_eyebrow'] ?? ''),
                'title' => (string)($_POST['tours_text_title'] ?? ''),
                'sub' => (string)($_POST['tours_text_sub'] ?? ''),
            ],
            'cases' => [
                'eyebrow' => (string)($_POST['cases_eyebrow'] ?? ''),
                'title' => (string)($_POST['cases_title_text'] ?? ''),
                'sub' => (string)($_POST['cases_sub'] ?? ''),
                'items' => $caseItems,
            ],
            'testimonials' => [
                'eyebrow' => (string)($_POST['testimonials_eyebrow'] ?? ''),
                'title' => (string)($_POST['testimonials_title'] ?? ''),
                'items' => $testiItems,
            ],
            'ablauf' => [
                'eyebrow' => (string)($_POST['ablauf_eyebrow'] ?? ''),
                'title' => (string)($_POST['ablauf_title_text'] ?? ''),
                'items' => $ablaufSteps,
            ],
            'kontakt_text' => [
                'eyebrow' => (string)($_POST['kontakt_eyebrow'] ?? ''),
                'title' => (string)($_POST['kontakt_title'] ?? ''),
                'sub' => (string)($_POST['kontakt_sub'] ?? ''),
                'sidebar_heading' => (string)($_POST['kontakt_sidebar_heading'] ?? ''),
                'sidebar_text' => (string)($_POST['kontakt_sidebar_text'] ?? ''),
                'email' => (string)($_POST['kontakt_email'] ?? ''),
                'response_label' => (string)($_POST['kontakt_response_label'] ?? ''),
                'response_text' => (string)($_POST['kontakt_response_text'] ?? ''),
                'location_label' => (string)($_POST['kontakt_location_label'] ?? ''),
                'location_text' => (string)($_POST['kontakt_location_text'] ?? ''),
            ],
            'faq' => [
                'eyebrow' => (string)($_POST['faq_eyebrow'] ?? ''),
                'title' => (string)($_POST['faq_title'] ?? ''),
                'sub' => (string)($_POST['faq_sub'] ?? ''),
                'items' => $faqItems,
                'button_text' => (string)($_POST['faq_button_text'] ?? ''),
            ],
            'partner' => [
                'hero_eyebrow' => (string)($_POST['partner_hero_eyebrow'] ?? ''),
                'hero_title' => (string)($_POST['partner_hero_title'] ?? ''),
                'hero_sub' => (string)($_POST['partner_hero_sub'] ?? ''),
                'hero_button_text' => (string)($_POST['partner_hero_button_text'] ?? ''),
                'proof_eyebrow' => (string)($_POST['partner_proof_eyebrow'] ?? ''),
                'proof_title' => (string)($_POST['partner_proof_title'] ?? ''),
                'proof_sub' => (string)($_POST['partner_proof_sub'] ?? ''),
                'card_1_kicker' => (string)($_POST['partner_card_1_kicker'] ?? ''),
                'card_1_title' => (string)($_POST['partner_card_1_title'] ?? ''),
                'card_1_text' => (string)($_POST['partner_card_1_text'] ?? ''),
                'card_2_kicker' => (string)($_POST['partner_card_2_kicker'] ?? ''),
                'card_2_title' => (string)($_POST['partner_card_2_title'] ?? ''),
                'card_2_text' => (string)($_POST['partner_card_2_text'] ?? ''),
                'card_3_kicker' => (string)($_POST['partner_card_3_kicker'] ?? ''),
                'card_3_title' => (string)($_POST['partner_card_3_title'] ?? ''),
                'card_3_text' => (string)($_POST['partner_card_3_text'] ?? ''),
                'fit_eyebrow' => (string)($_POST['partner_fit_eyebrow'] ?? ''),
                'fit_title' => (string)($_POST['partner_fit_title'] ?? ''),
                'fit_text' => (string)($_POST['partner_fit_text'] ?? ''),
                'fit_note' => (string)($_POST['partner_fit_note'] ?? ''),
                'form_eyebrow' => (string)($_POST['partner_form_eyebrow'] ?? ''),
                'form_title' => (string)($_POST['partner_form_title'] ?? ''),
                'form_sub' => (string)($_POST['partner_form_sub'] ?? ''),
                'form_name_label' => (string)($_POST['partner_form_name_label'] ?? ''),
                'form_name_placeholder' => (string)($_POST['partner_form_name_placeholder'] ?? ''),
                'form_company_label' => (string)($_POST['partner_form_company_label'] ?? ''),
                'form_company_placeholder' => (string)($_POST['partner_form_company_placeholder'] ?? ''),
                'form_email_label' => (string)($_POST['partner_form_email_label'] ?? ''),
                'form_email_placeholder' => (string)($_POST['partner_form_email_placeholder'] ?? ''),
                'form_role_label' => (string)($_POST['partner_form_role_label'] ?? ''),
                'form_role_placeholder' => (string)($_POST['partner_form_role_placeholder'] ?? ''),
                'form_role_option_1' => (string)($_POST['partner_form_role_option_1'] ?? ''),
                'form_role_option_2' => (string)($_POST['partner_form_role_option_2'] ?? ''),
                'form_role_option_3' => (string)($_POST['partner_form_role_option_3'] ?? ''),
                'form_role_option_4' => (string)($_POST['partner_form_role_option_4'] ?? ''),
                'form_message_label' => (string)($_POST['partner_form_message_label'] ?? ''),
                'form_message_placeholder' => (string)($_POST['partner_form_message_placeholder'] ?? ''),
                'form_privacy_prefix' => (string)($_POST['partner_form_privacy_prefix'] ?? ''),
                'form_privacy_link' => (string)($_POST['partner_form_privacy_link'] ?? ''),
                'form_privacy_suffix' => (string)($_POST['partner_form_privacy_suffix'] ?? ''),
                'form_submit_text' => (string)($_POST['partner_form_submit_text'] ?? ''),
            ],
        ];

        if (!admin_write_json($contentPath, $newContent)) {
            $error = 'Inhaltsdaten konnten nicht gespeichert werden.';
        } else {
            $notice = 'Inhalte erfolgreich gespeichert.';
            $content = $newContent;
        }
    }
}

// Reload content after save
$content = admin_read_json($contentPath, []);
$section = preg_replace('/[^a-z0-9_]/', '', (string)($_GET['s'] ?? 'seo'));

// Prepare repeater arrays
$kpiFormItems = array_values(array_filter($content['kpi']['items'] ?? [], fn($i) => is_array($i)));
if (!$kpiFormItems) $kpiFormItems = [['target' => '', 'suffix' => '', 'label' => '']];

$w360FormCards = array_values(array_filter($content['warum360']['cards'] ?? [], fn($i) => is_array($i)));
if (!$w360FormCards) $w360FormCards = [['emoji' => '', 'title' => '', 'text' => '']];

$caseFormItems = array_values(array_filter($content['cases']['items'] ?? [], fn($i) => is_array($i)));
if (!$caseFormItems) $caseFormItems = [['tag' => '', 'title' => '', 'desc' => '', 'stat1_value' => '', 'stat1_label' => '', 'stat2_value' => '', 'stat2_label' => '']];

$testiFormItems = array_values(array_filter($content['testimonials']['items'] ?? [], fn($i) => is_array($i)));
if (!$testiFormItems) $testiFormItems = [['text' => '', 'author' => '', 'company' => '']];

$ablaufFormSteps = array_values(array_filter($content['ablauf']['items'] ?? [], fn($i) => is_array($i)));
if (!$ablaufFormSteps) $ablaufFormSteps = [['emoji' => '', 'title' => '', 'text' => '']];

$faqFormItems = array_values(array_filter($content['faq']['items'] ?? [], fn($i) => is_array($i)));
if (!$faqFormItems) $faqFormItems = [['question' => '', 'answer' => '']];

$vergleichNegItems = array_values(array_filter($content['vergleich']['negative_items'] ?? [], fn($v) => is_string($v) && $v !== ''));
if (!$vergleichNegItems) $vergleichNegItems = [''];

$vergleichPosItems = array_values(array_filter($content['vergleich']['positive_items'] ?? [], fn($v) => is_string($v) && $v !== ''));
if (!$vergleichPosItems) $vergleichPosItems = [''];

$buttonFxAllowed = [
    'kontakt_submit' => 'Kontaktformular: Absenden',
    'partner_submit' => 'Partnerformular: Absenden',
    'hero_primary' => 'Hero: Primärbutton',
    'hero_secondary' => 'Hero: Sekundärbutton',
    'cta_primary' => 'Final CTA: Primärbutton',
    'cta_secondary' => 'Final CTA: Sekundärbutton',
];
$buttonFxTargets = array_values(array_filter($content['button_fx']['targets'] ?? [], fn($v) => is_string($v) && isset($buttonFxAllowed[$v])));
$buttonFxColor = admin_field($content, 'button_fx.color', '#8ec9ff');
if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $buttonFxColor)) $buttonFxColor = '#8ec9ff';

// Sub-nav structure
$subnav = [
    'STARTSEITE' => [
        'seo' => 'SEO',
        'hero' => 'Hero',
        'mockup' => 'Mockup',
        'vergleich' => 'Vergleich',
        'warum360' => 'Warum 360°',
    ],
    'ABSCHNITTE' => [
        'kpi' => 'KPI',
        'marquee' => 'Marquee',
        'livedemos' => 'Live-Demos',
        'cases' => 'Cases',
        'testimonials' => 'Testimonials',
        'ablauf' => 'Ablauf',
    ],
    'SEITEN' => [
        'about' => 'About',
        'team' => 'Team',
        'kontakt' => 'Kontakt',
        'faq' => 'FAQ',
        'partner' => 'Partner',
    ],
    'DESIGN' => [
        'finalcta' => 'Final CTA',
        'footer' => 'Footer',
        'buttonstyle' => 'Button-Stile',
        'intro' => 'Intro',
    ],
];

ob_start();
?>
<div class="topbar">
  <div class="topbar-title">Inhalte</div>
  <div class="topbar-actions">
    <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">Vorschau ↗</a>
    <button type="submit" form="main-form" class="btn btn-primary btn-sm">Speichern</button>
  </div>
</div>

<div class="page-body has-action-bar">
  <div class="content-layout">

    <!-- Sub Navigation -->
    <div class="subnav">
      <?php foreach ($subnav as $groupLabel => $items): ?>
      <div class="subnav-group">
        <div class="subnav-group-label"><?= he($groupLabel) ?></div>
        <?php foreach ($items as $slug => $label): ?>
        <a href="?p=content&s=<?= he($slug) ?>" class="subnav-item <?= $section === $slug ? 'active' : '' ?>">
          <?= he($label) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Content Form -->
    <div>
      <form id="main-form" method="post" action="?p=content&s=<?= he($section) ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= he($csrf) ?>">
        <input type="hidden" name="action" value="save_content">

        <!-- ── SEO ─────────────────────────────────────────── -->
        <div <?= $section !== 'seo' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">SEO</div>
                <div class="card-desc" style="margin-bottom:0">Meta-Titel und Beschreibung für die Startseite</div>
              </div>
            </div>
            <div class="field">
              <label>Seiten-Titel (meta title)</label>
              <input type="text" name="seo_home_title" value="<?= he(admin_field($content, 'seo.home_title')) ?>">
            </div>
            <div class="field mt-12">
              <label>Meta-Beschreibung</label>
              <textarea name="seo_home_desc"><?= he(admin_field($content, 'seo.home_desc')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- ── Hero ───────────────────────────────────────── -->
        <div <?= $section !== 'hero' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Hero</div>
                <div class="card-desc" style="margin-bottom:0">Hauptbereich der Startseite</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow (über dem Titel)</label>
                <input type="text" name="hero_eyebrow" value="<?= he(admin_field($content, 'hero.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Prefix (vor dem rotierenden Wort)</label>
                <input type="text" name="hero_prefix" value="<?= he(admin_field($content, 'hero.prefix')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Rotierende Wörter (eines pro Zeile)</label>
              <textarea name="hero_rotating_words"><?= he(admin_lines($content, 'hero.rotating_words')) ?></textarea>
            </div>
            <div class="field mt-12">
              <label>Beschreibungstext</label>
              <textarea name="hero_desc"><?= he(admin_field($content, 'hero.desc')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="form-row">
              <div class="field">
                <label>Primärbutton Text</label>
                <input type="text" name="hero_btn1_text" value="<?= he(admin_field($content, 'hero.button_primary_text')) ?>">
              </div>
              <div class="field">
                <label>Primärbutton Link</label>
                <input type="text" name="hero_btn1_link" value="<?= he(admin_field($content, 'hero.button_primary_link')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Sekundärbutton Text</label>
                <input type="text" name="hero_btn2_text" value="<?= he(admin_field($content, 'hero.button_secondary_text')) ?>">
              </div>
              <div class="field">
                <label>Sekundärbutton Link</label>
                <input type="text" name="hero_btn2_link" value="<?= he(admin_field($content, 'hero.button_secondary_link')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── Mockup ──────────────────────────────────────── -->
        <div <?= $section !== 'mockup' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Mockup</div>
                <div class="card-desc" style="margin-bottom:0">Mockup-Abschnitt mit Gerätegrafiken</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="mockup_text_eyebrow" value="<?= he(admin_field($content, 'mockup_text.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="mockup_text_title" value="<?= he(admin_field($content, 'mockup_text.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="mockup_text_sub"><?= he(admin_field($content, 'mockup_text.sub')) ?></textarea>
            </div>
            <hr class="divider">
            <?php foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'phone' => 'Smartphone'] as $mk => $mlabel): ?>
            <div class="form-row mt-12" style="align-items:end">
              <div class="field">
                <label><?= $mlabel ?> Bild (Upload)</label>
                <input type="file" name="mockup_<?= $mk ?>_file" accept="image/*">
              </div>
              <div class="field">
                <label><?= $mlabel ?> Bildpfad</label>
                <input type="text" name="mockup_<?= $mk ?>_img" value="<?= he(admin_field($content, 'mockup.' . $mk . '_img')) ?>">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- ── Vergleich ───────────────────────────────────── -->
        <div <?= $section !== 'vergleich' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Vergleich</div>
                <div class="card-desc" style="margin-bottom:0">Vorher/Nachher Vergleichsabschnitt</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="vergleich_eyebrow" value="<?= he(admin_field($content, 'vergleich.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="vergleich_title" value="<?= he(admin_field($content, 'vergleich.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="vergleich_sub"><?= he(admin_field($content, 'vergleich.sub')) ?></textarea>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Badge Negativ</label>
                <input type="text" name="vergleich_badge_negative" value="<?= he(admin_field($content, 'vergleich.badge_negative')) ?>">
              </div>
              <div class="field">
                <label>Badge Positiv</label>
                <input type="text" name="vergleich_badge_positive" value="<?= he(admin_field($content, 'vergleich.badge_positive')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Negative Punkte</div>
            <div class="repeater" id="vergleich-neg-repeater">
              <?php foreach ($vergleichNegItems as $idx => $negItem): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Punkt <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="field">
                    <label>Text</label>
                    <input type="text" name="vergleich_neg_item[]" value="<?= he((string)$negItem) ?>">
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="vergleich-neg-repeater"
              data-fields='[{"name":"vergleich_neg_item[]","label":"Text","type":"text"}]'>+ Negativpunkt hinzufügen</button>

            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Positive Punkte</div>
            <div class="repeater" id="vergleich-pos-repeater">
              <?php foreach ($vergleichPosItems as $idx => $posItem): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Punkt <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="field">
                    <label>Text</label>
                    <input type="text" name="vergleich_pos_item[]" value="<?= he((string)$posItem) ?>">
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="vergleich-pos-repeater"
              data-fields='[{"name":"vergleich_pos_item[]","label":"Text","type":"text"}]'>+ Positivpunkt hinzufügen</button>
          </div>
        </div>

        <!-- ── Warum 360° ──────────────────────────────────── -->
        <div <?= $section !== 'warum360' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Warum 360°</div>
                <div class="card-desc" style="margin-bottom:0">Scroll-Stack Abschnitt mit Feature-Karten</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="warum360_eyebrow" value="<?= he(admin_field($content, 'warum360.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="warum360_title_text" value="<?= he(admin_field($content, 'warum360.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="warum360_sub"><?= he(admin_field($content, 'warum360.sub')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Feature-Karten</div>
            <div class="repeater" id="warum360-repeater">
              <?php foreach ($w360FormCards as $idx => $card): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Karte <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="form-row">
                    <div class="field">
                      <label>Emoji</label>
                      <input type="text" name="warum360_emoji[]" value="<?= he($card['emoji'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Titel</label>
                      <input type="text" name="warum360_card_title[]" value="<?= he($card['title'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="field">
                    <label>Text</label>
                    <textarea name="warum360_card_text[]"><?= he($card['text'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="warum360-repeater"
              data-fields='[{"name":"warum360_emoji[]","label":"Emoji","type":"text"},{"name":"warum360_card_title[]","label":"Titel","type":"text"},{"name":"warum360_card_text[]","label":"Text","type":"textarea"}]'>+ Karte hinzufügen</button>
          </div>
        </div>

        <!-- ── KPI ────────────────────────────────────────── -->
        <div <?= $section !== 'kpi' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">KPI</div>
                <div class="card-desc" style="margin-bottom:0">Kennzahlen-Abschnitt</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="kpi_eyebrow" value="<?= he(admin_field($content, 'kpi.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="kpi_title" value="<?= he(admin_field($content, 'kpi.title')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">KPI Einträge</div>
            <div class="repeater" id="kpi-repeater">
              <?php foreach ($kpiFormItems as $idx => $kpi): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">KPI <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="form-row-3">
                    <div class="field">
                      <label>Zielwert</label>
                      <input type="text" name="kpi_target[]" value="<?= he($kpi['target'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Suffix (z.B. %)</label>
                      <input type="text" name="kpi_suffix[]" value="<?= he($kpi['suffix'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Bezeichnung</label>
                      <input type="text" name="kpi_label[]" value="<?= he($kpi['label'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="kpi-repeater"
              data-fields='[{"name":"kpi_target[]","label":"Zielwert","type":"text"},{"name":"kpi_suffix[]","label":"Suffix","type":"text"},{"name":"kpi_label[]","label":"Bezeichnung","type":"text"}]'>+ KPI hinzufügen</button>
          </div>
        </div>

        <!-- ── Marquee ─────────────────────────────────────── -->
        <div <?= $section !== 'marquee' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Marquee</div>
                <div class="card-desc" style="margin-bottom:0">Lauftext-Abschnitt zwischen den Sektionen</div>
              </div>
            </div>
            <div class="field">
              <label>Marquee Label</label>
              <input type="text" name="marquee_label" value="<?= he(admin_field($content, 'marquee.label')) ?>">
            </div>
          </div>
        </div>

        <!-- ── Live-Demos ──────────────────────────────────── -->
        <div <?= $section !== 'livedemos' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Live-Demos</div>
                <div class="card-desc" style="margin-bottom:0">Text über dem Rundgänge-Abschnitt</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="tours_text_eyebrow" value="<?= he(admin_field($content, 'tours_text.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="tours_text_title" value="<?= he(admin_field($content, 'tours_text.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="tours_text_sub"><?= he(admin_field($content, 'tours_text.sub')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- ── Cases ──────────────────────────────────────── -->
        <div <?= $section !== 'cases' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Case Studies</div>
                <div class="card-desc" style="margin-bottom:0">Fallbeispiele mit Statistiken</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="cases_eyebrow" value="<?= he(admin_field($content, 'cases.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="cases_title_text" value="<?= he(admin_field($content, 'cases.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="cases_sub"><?= he(admin_field($content, 'cases.sub')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Case-Einträge</div>
            <div class="repeater" id="cases-repeater">
              <?php foreach ($caseFormItems as $idx => $case): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Case <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="form-row">
                    <div class="field">
                      <label>Tag / Branche</label>
                      <input type="text" name="case_tag[]" value="<?= he($case['tag'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Titel</label>
                      <input type="text" name="case_title[]" value="<?= he($case['title'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="field mt-8">
                    <label>Beschreibung</label>
                    <textarea name="case_desc[]"><?= he($case['desc'] ?? '') ?></textarea>
                  </div>
                  <div class="form-row mt-8">
                    <div class="field">
                      <label>Statistik 1 Wert</label>
                      <input type="text" name="case_stat1_value[]" value="<?= he($case['stat1_value'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Statistik 1 Bezeichnung</label>
                      <input type="text" name="case_stat1_label[]" value="<?= he($case['stat1_label'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="form-row mt-8">
                    <div class="field">
                      <label>Statistik 2 Wert</label>
                      <input type="text" name="case_stat2_value[]" value="<?= he($case['stat2_value'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Statistik 2 Bezeichnung</label>
                      <input type="text" name="case_stat2_label[]" value="<?= he($case['stat2_label'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="cases-repeater"
              data-fields='[{"name":"case_tag[]","label":"Tag","type":"text"},{"name":"case_title[]","label":"Titel","type":"text"},{"name":"case_desc[]","label":"Beschreibung","type":"textarea"},{"name":"case_stat1_value[]","label":"Stat 1 Wert","type":"text"},{"name":"case_stat1_label[]","label":"Stat 1 Label","type":"text"},{"name":"case_stat2_value[]","label":"Stat 2 Wert","type":"text"},{"name":"case_stat2_label[]","label":"Stat 2 Label","type":"text"}]'>+ Case hinzufügen</button>
          </div>
        </div>

        <!-- ── Testimonials ────────────────────────────────── -->
        <div <?= $section !== 'testimonials' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Testimonials</div>
                <div class="card-desc" style="margin-bottom:0">Kundenstimmen</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="testimonials_eyebrow" value="<?= he(admin_field($content, 'testimonials.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="testimonials_title" value="<?= he(admin_field($content, 'testimonials.title')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Stimmen</div>
            <div class="repeater" id="testi-repeater">
              <?php foreach ($testiFormItems as $idx => $testi): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Stimme <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="field">
                    <label>Zitat</label>
                    <textarea name="testi_text[]"><?= he($testi['text'] ?? '') ?></textarea>
                  </div>
                  <div class="form-row mt-8">
                    <div class="field">
                      <label>Autor</label>
                      <input type="text" name="testi_author[]" value="<?= he($testi['author'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Unternehmen</label>
                      <input type="text" name="testi_company[]" value="<?= he($testi['company'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="testi-repeater"
              data-fields='[{"name":"testi_text[]","label":"Zitat","type":"textarea"},{"name":"testi_author[]","label":"Autor","type":"text"},{"name":"testi_company[]","label":"Unternehmen","type":"text"}]'>+ Stimme hinzufügen</button>
          </div>
        </div>

        <!-- ── Ablauf ──────────────────────────────────────── -->
        <div <?= $section !== 'ablauf' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Ablauf</div>
                <div class="card-desc" style="margin-bottom:0">Prozessschritte</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="ablauf_eyebrow" value="<?= he(admin_field($content, 'ablauf.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="ablauf_title_text" value="<?= he(admin_field($content, 'ablauf.title')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Schritte</div>
            <div class="repeater" id="ablauf-repeater">
              <?php foreach ($ablaufFormSteps as $idx => $step): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Schritt <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="form-row">
                    <div class="field">
                      <label>Emoji</label>
                      <input type="text" name="ablauf_emoji[]" value="<?= he($step['emoji'] ?? '') ?>">
                    </div>
                    <div class="field">
                      <label>Titel</label>
                      <input type="text" name="ablauf_step_title[]" value="<?= he($step['title'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="field mt-8">
                    <label>Text</label>
                    <textarea name="ablauf_step_text[]"><?= he($step['text'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="ablauf-repeater"
              data-fields='[{"name":"ablauf_emoji[]","label":"Emoji","type":"text"},{"name":"ablauf_step_title[]","label":"Titel","type":"text"},{"name":"ablauf_step_text[]","label":"Text","type":"textarea"}]'>+ Schritt hinzufügen</button>
          </div>
        </div>

        <!-- ── About ──────────────────────────────────────── -->
        <div <?= $section !== 'about' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">About</div>
                <div class="card-desc" style="margin-bottom:0">Über uns Seite</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="about_eyebrow" value="<?= he(admin_field($content, 'about.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="about_title" value="<?= he(admin_field($content, 'about.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Absatz 1</label>
              <textarea name="about_p1"><?= he(admin_field($content, 'about.paragraph_1')) ?></textarea>
            </div>
            <div class="field mt-12">
              <label>Absatz 2</label>
              <textarea name="about_p2"><?= he(admin_field($content, 'about.paragraph_2')) ?></textarea>
            </div>
            <div class="field mt-12">
              <label>Absatz 3</label>
              <textarea name="about_p3"><?= he(admin_field($content, 'about.paragraph_3')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="form-row">
              <div class="field">
                <label>Panel Badge</label>
                <input type="text" name="about_panel_badge" value="<?= he(admin_field($content, 'about.panel_badge')) ?>">
              </div>
              <div class="field">
                <label>Differenz-Titel</label>
                <input type="text" name="about_difference_title" value="<?= he(admin_field($content, 'about.difference_title')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Features Titel</label>
                <input type="text" name="about_features_title" value="<?= he(admin_field($content, 'about.features_title')) ?>">
              </div>
              <div class="field">
                <label>Perfektion Titel</label>
                <input type="text" name="about_perfection_title" value="<?= he(admin_field($content, 'about.perfection_title')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Features (eine pro Zeile)</label>
                <textarea name="about_features"><?= he(admin_lines($content, 'about.features')) ?></textarea>
              </div>
              <div class="field">
                <label>Perfektion Punkte (eine pro Zeile)</label>
                <textarea name="about_perfection_points"><?= he(admin_lines($content, 'about.perfection_points')) ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Team ───────────────────────────────────────── -->
        <div <?= $section !== 'team' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Team</div>
                <div class="card-desc" style="margin-bottom:0">Team-Mitglieder auf der About-Seite</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="team_eyebrow" value="<?= he(admin_field($content, 'team.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="team_title" value="<?= he(admin_field($content, 'team.title')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:12px">Kristian</div>
            <div class="form-row">
              <div class="field">
                <label>Name</label>
                <input type="text" name="team_kristian_name" value="<?= he(admin_field($content, 'team.kristian_name')) ?>">
              </div>
              <div class="field">
                <label>Rolle</label>
                <input type="text" name="team_kristian_role" value="<?= he(admin_field($content, 'team.kristian_role')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Bio Text</label>
              <textarea name="team_kristian_text"><?= he(admin_field($content, 'team.kristian_text')) ?></textarea>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Foto hochladen</label>
                <input type="file" name="team_kristian_photo_file" accept="image/*">
              </div>
              <div class="field">
                <label>Foto Pfad</label>
                <input type="text" name="team_kristian_photo" value="<?= he(admin_field($content, 'team.kristian_photo')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:12px">Jason</div>
            <div class="form-row">
              <div class="field">
                <label>Name</label>
                <input type="text" name="team_jason_name" value="<?= he(admin_field($content, 'team.jason_name')) ?>">
              </div>
              <div class="field">
                <label>Rolle</label>
                <input type="text" name="team_jason_role" value="<?= he(admin_field($content, 'team.jason_role')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Bio Text</label>
              <textarea name="team_jason_text"><?= he(admin_field($content, 'team.jason_text')) ?></textarea>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Foto hochladen</label>
                <input type="file" name="team_jason_photo_file" accept="image/*">
              </div>
              <div class="field">
                <label>Foto Pfad</label>
                <input type="text" name="team_jason_photo" value="<?= he(admin_field($content, 'team.jason_photo')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Link Text</label>
                <input type="text" name="team_jason_link_text" value="<?= he(admin_field($content, 'team.jason_link_text')) ?>">
              </div>
              <div class="field">
                <label>Link URL</label>
                <input type="text" name="team_jason_link_url" value="<?= he(admin_field($content, 'team.jason_link_url')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── Kontakt ─────────────────────────────────────── -->
        <div <?= $section !== 'kontakt' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Kontakt</div>
                <div class="card-desc" style="margin-bottom:0">Kontaktseite Texte</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="kontakt_eyebrow" value="<?= he(admin_field($content, 'kontakt_text.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="kontakt_title" value="<?= he(admin_field($content, 'kontakt_text.title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="kontakt_sub"><?= he(admin_field($content, 'kontakt_text.sub')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="form-row">
              <div class="field">
                <label>Sidebar Überschrift</label>
                <input type="text" name="kontakt_sidebar_heading" value="<?= he(admin_field($content, 'kontakt_text.sidebar_heading')) ?>">
              </div>
              <div class="field">
                <label>E-Mail</label>
                <input type="email" name="kontakt_email" value="<?= he(admin_field($content, 'kontakt_text.email')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Sidebar Text</label>
              <textarea name="kontakt_sidebar_text"><?= he(admin_field($content, 'kontakt_text.sidebar_text')) ?></textarea>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Antwortzeit Label</label>
                <input type="text" name="kontakt_response_label" value="<?= he(admin_field($content, 'kontakt_text.response_label')) ?>">
              </div>
              <div class="field">
                <label>Antwortzeit Text</label>
                <input type="text" name="kontakt_response_text" value="<?= he(admin_field($content, 'kontakt_text.response_text')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Standort Label</label>
                <input type="text" name="kontakt_location_label" value="<?= he(admin_field($content, 'kontakt_text.location_label')) ?>">
              </div>
              <div class="field">
                <label>Standort Text</label>
                <input type="text" name="kontakt_location_text" value="<?= he(admin_field($content, 'kontakt_text.location_text')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── FAQ ────────────────────────────────────────── -->
        <div <?= $section !== 'faq' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">FAQ</div>
                <div class="card-desc" style="margin-bottom:0">Häufig gestellte Fragen</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="faq_eyebrow" value="<?= he(admin_field($content, 'faq.eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="faq_title" value="<?= he(admin_field($content, 'faq.title')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Untertitel</label>
                <textarea name="faq_sub"><?= he(admin_field($content, 'faq.sub')) ?></textarea>
              </div>
              <div class="field">
                <label>Button Text</label>
                <input type="text" name="faq_button_text" value="<?= he(admin_field($content, 'faq.button_text')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">FAQ Einträge</div>
            <div class="repeater" id="faq-repeater">
              <?php foreach ($faqFormItems as $idx => $faq): ?>
              <div class="r-item">
                <div class="r-item-head">
                  <span class="r-handle">⠿</span>
                  <span class="r-label">Frage <?= $idx + 1 ?></span>
                  <button type="button" class="r-remove btn btn-danger btn-xs">✕</button>
                </div>
                <div class="r-item-body">
                  <div class="field">
                    <label>Frage</label>
                    <input type="text" name="faq_question[]" value="<?= he($faq['question'] ?? '') ?>">
                  </div>
                  <div class="field mt-8">
                    <label>Antwort</label>
                    <textarea name="faq_answer[]"><?= he($faq['answer'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="r-add" data-repeater="faq-repeater"
              data-fields='[{"name":"faq_question[]","label":"Frage","type":"text"},{"name":"faq_answer[]","label":"Antwort","type":"textarea"}]'>+ Frage hinzufügen</button>
          </div>
        </div>

        <!-- ── Partner ─────────────────────────────────────── -->
        <div <?= $section !== 'partner' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Partner</div>
                <div class="card-desc" style="margin-bottom:0">Partner-Seite Texte</div>
              </div>
            </div>
            <div class="card-title" style="margin-bottom:10px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2)">Hero</div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="partner_hero_eyebrow" value="<?= he(admin_field($content, 'partner.hero_eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="partner_hero_title" value="<?= he(admin_field($content, 'partner.hero_title')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Untertitel</label>
                <textarea name="partner_hero_sub"><?= he(admin_field($content, 'partner.hero_sub')) ?></textarea>
              </div>
              <div class="field">
                <label>Button Text</label>
                <input type="text" name="partner_hero_button_text" value="<?= he(admin_field($content, 'partner.hero_button_text')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2)">Proof Sektion</div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="partner_proof_eyebrow" value="<?= he(admin_field($content, 'partner.proof_eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="partner_proof_title" value="<?= he(admin_field($content, 'partner.proof_title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="partner_proof_sub"><?= he(admin_field($content, 'partner.proof_sub')) ?></textarea>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2)">Karten</div>
            <?php for ($ci = 1; $ci <= 3; $ci++): ?>
            <div class="form-row-3 mt-12">
              <div class="field">
                <label>Karte <?= $ci ?> Kicker</label>
                <input type="text" name="partner_card_<?= $ci ?>_kicker" value="<?= he(admin_field($content, 'partner.card_' . $ci . '_kicker')) ?>">
              </div>
              <div class="field">
                <label>Karte <?= $ci ?> Titel</label>
                <input type="text" name="partner_card_<?= $ci ?>_title" value="<?= he(admin_field($content, 'partner.card_' . $ci . '_title')) ?>">
              </div>
              <div class="field">
                <label>Karte <?= $ci ?> Text</label>
                <input type="text" name="partner_card_<?= $ci ?>_text" value="<?= he(admin_field($content, 'partner.card_' . $ci . '_text')) ?>">
              </div>
            </div>
            <?php endfor; ?>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2)">Fit Sektion</div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="partner_fit_eyebrow" value="<?= he(admin_field($content, 'partner.fit_eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="partner_fit_title" value="<?= he(admin_field($content, 'partner.fit_title')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Text</label>
                <textarea name="partner_fit_text"><?= he(admin_field($content, 'partner.fit_text')) ?></textarea>
              </div>
              <div class="field">
                <label>Hinweis</label>
                <textarea name="partner_fit_note"><?= he(admin_field($content, 'partner.fit_note')) ?></textarea>
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2)">Formular</div>
            <div class="form-row">
              <div class="field">
                <label>Eyebrow</label>
                <input type="text" name="partner_form_eyebrow" value="<?= he(admin_field($content, 'partner.form_eyebrow')) ?>">
              </div>
              <div class="field">
                <label>Titel</label>
                <input type="text" name="partner_form_title" value="<?= he(admin_field($content, 'partner.form_title')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Untertitel</label>
              <textarea name="partner_form_sub"><?= he(admin_field($content, 'partner.form_sub')) ?></textarea>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Name Label</label>
                <input type="text" name="partner_form_name_label" value="<?= he(admin_field($content, 'partner.form_name_label')) ?>">
              </div>
              <div class="field">
                <label>Name Placeholder</label>
                <input type="text" name="partner_form_name_placeholder" value="<?= he(admin_field($content, 'partner.form_name_placeholder')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Firma Label</label>
                <input type="text" name="partner_form_company_label" value="<?= he(admin_field($content, 'partner.form_company_label')) ?>">
              </div>
              <div class="field">
                <label>Firma Placeholder</label>
                <input type="text" name="partner_form_company_placeholder" value="<?= he(admin_field($content, 'partner.form_company_placeholder')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>E-Mail Label</label>
                <input type="text" name="partner_form_email_label" value="<?= he(admin_field($content, 'partner.form_email_label')) ?>">
              </div>
              <div class="field">
                <label>E-Mail Placeholder</label>
                <input type="text" name="partner_form_email_placeholder" value="<?= he(admin_field($content, 'partner.form_email_placeholder')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Rolle Label</label>
                <input type="text" name="partner_form_role_label" value="<?= he(admin_field($content, 'partner.form_role_label')) ?>">
              </div>
              <div class="field">
                <label>Rolle Placeholder</label>
                <input type="text" name="partner_form_role_placeholder" value="<?= he(admin_field($content, 'partner.form_role_placeholder')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Rolle Option 1</label>
                <input type="text" name="partner_form_role_option_1" value="<?= he(admin_field($content, 'partner.form_role_option_1')) ?>">
              </div>
              <div class="field">
                <label>Rolle Option 2</label>
                <input type="text" name="partner_form_role_option_2" value="<?= he(admin_field($content, 'partner.form_role_option_2')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Rolle Option 3</label>
                <input type="text" name="partner_form_role_option_3" value="<?= he(admin_field($content, 'partner.form_role_option_3')) ?>">
              </div>
              <div class="field">
                <label>Rolle Option 4</label>
                <input type="text" name="partner_form_role_option_4" value="<?= he(admin_field($content, 'partner.form_role_option_4')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Nachricht Label</label>
                <input type="text" name="partner_form_message_label" value="<?= he(admin_field($content, 'partner.form_message_label')) ?>">
              </div>
              <div class="field">
                <label>Nachricht Placeholder</label>
                <input type="text" name="partner_form_message_placeholder" value="<?= he(admin_field($content, 'partner.form_message_placeholder')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Datenschutz Prefix</label>
                <input type="text" name="partner_form_privacy_prefix" value="<?= he(admin_field($content, 'partner.form_privacy_prefix')) ?>">
              </div>
              <div class="field">
                <label>Datenschutz Link-Text</label>
                <input type="text" name="partner_form_privacy_link" value="<?= he(admin_field($content, 'partner.form_privacy_link')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Datenschutz Suffix</label>
                <input type="text" name="partner_form_privacy_suffix" value="<?= he(admin_field($content, 'partner.form_privacy_suffix')) ?>">
              </div>
              <div class="field">
                <label>Absenden Button Text</label>
                <input type="text" name="partner_form_submit_text" value="<?= he(admin_field($content, 'partner.form_submit_text')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── Final CTA ───────────────────────────────────── -->
        <div <?= $section !== 'finalcta' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Final CTA</div>
                <div class="card-desc" style="margin-bottom:0">Letzter Call-to-Action Bereich</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Titel</label>
                <input type="text" name="cta_title" value="<?= he(admin_field($content, 'final_cta.title')) ?>">
              </div>
              <div class="field">
                <label>Text</label>
                <input type="text" name="cta_text" value="<?= he(admin_field($content, 'final_cta.text')) ?>">
              </div>
            </div>
            <hr class="divider">
            <div class="form-row">
              <div class="field">
                <label>Primärbutton Text</label>
                <input type="text" name="cta_btn1_text" value="<?= he(admin_field($content, 'final_cta.button_primary_text')) ?>">
              </div>
              <div class="field">
                <label>Primärbutton Link</label>
                <input type="text" name="cta_btn1_link" value="<?= he(admin_field($content, 'final_cta.button_primary_link')) ?>">
              </div>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Sekundärbutton Text</label>
                <input type="text" name="cta_btn2_text" value="<?= he(admin_field($content, 'final_cta.button_secondary_text')) ?>">
              </div>
              <div class="field">
                <label>Sekundärbutton Link</label>
                <input type="text" name="cta_btn2_link" value="<?= he(admin_field($content, 'final_cta.button_secondary_link')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── Footer ──────────────────────────────────────── -->
        <div <?= $section !== 'footer' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Footer</div>
                <div class="card-desc" style="margin-bottom:0">Footer-Texte und Links</div>
              </div>
            </div>
            <div class="field">
              <label>Brand Text</label>
              <textarea name="footer_brand_text"><?= he(admin_field($content, 'footer.brand_text')) ?></textarea>
            </div>
            <div class="field mt-12">
              <label>Kontakt E-Mail</label>
              <input type="email" name="footer_contact_email" value="<?= he(admin_field($content, 'footer.contact_email')) ?>">
            </div>
            <hr class="divider">
            <div class="form-row-3">
              <div class="field">
                <label>Website-by Prefix</label>
                <input type="text" name="footer_website_by_prefix" value="<?= he(admin_field($content, 'footer.website_by_prefix')) ?>">
              </div>
              <div class="field">
                <label>Website-by Name</label>
                <input type="text" name="footer_website_by_name" value="<?= he(admin_field($content, 'footer.website_by_name')) ?>">
              </div>
              <div class="field">
                <label>Website-by URL</label>
                <input type="url" name="footer_website_by_url" value="<?= he(admin_field($content, 'footer.website_by_url')) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- ── Button-Stile ────────────────────────────────── -->
        <div <?= $section !== 'buttonstyle' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Button-Stile</div>
                <div class="card-desc" style="margin-bottom:0">Shimmer-Effekt für Buttons</div>
              </div>
            </div>
            <div class="checkbox-row">
              <input type="checkbox" id="bfx_enabled" name="button_fx_enabled" value="1" <?= admin_bool_field($content, 'button_fx.enabled') ? 'checked' : '' ?>>
              <label for="bfx_enabled">Button FX aktivieren</label>
            </div>
            <div class="checkbox-row mt-8">
              <input type="checkbox" id="bfx_shimmer" name="button_fx_shimmer" value="1" <?= admin_bool_field($content, 'button_fx.shimmer') ? 'checked' : '' ?>>
              <label for="bfx_shimmer">Shimmer-Effekt aktivieren</label>
            </div>
            <div class="form-row mt-12">
              <div class="field">
                <label>Effekt Farbe</label>
                <div style="display:flex;gap:8px;align-items:center">
                  <input type="color" name="button_fx_color" id="bfx_color_picker" value="<?= he($buttonFxColor) ?>" style="width:50px;flex-shrink:0">
                  <input type="text" id="bfx_color_text" value="<?= he($buttonFxColor) ?>" style="font-family:monospace" maxlength="7">
                </div>
              </div>
            </div>
            <hr class="divider">
            <div class="card-title" style="margin-bottom:10px">Betroffene Buttons</div>
            <?php foreach ($buttonFxAllowed as $key => $label): ?>
            <div class="checkbox-row">
              <input type="checkbox" id="bfxt_<?= he($key) ?>" name="button_fx_targets[]" value="<?= he($key) ?>" <?= in_array($key, $buttonFxTargets, true) ? 'checked' : '' ?>>
              <label for="bfxt_<?= he($key) ?>"><?= he($label) ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- ── Intro ───────────────────────────────────────── -->
        <div <?= $section !== 'intro' ? 'style="display:none"' : '' ?>>
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Intro</div>
                <div class="card-desc" style="margin-bottom:0">Intro-Animation Texte</div>
              </div>
            </div>
            <div class="form-row">
              <div class="field">
                <label>Tagline</label>
                <input type="text" name="intro_tagline" value="<?= he(admin_field($content, 'intro.tagline')) ?>">
              </div>
              <div class="field">
                <label>Hint Text</label>
                <input type="text" name="intro_hint" value="<?= he(admin_field($content, 'intro.hint')) ?>">
              </div>
            </div>
            <div class="field mt-12">
              <label>Skip Button Text</label>
              <input type="text" name="intro_skip_button" value="<?= he(admin_field($content, 'intro.skip_button')) ?>">
            </div>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
// ── Color picker sync ──────────────────────────────────────────────
(function() {
  var picker = document.getElementById('bfx_color_picker');
  var text = document.getElementById('bfx_color_text');
  if (!picker || !text) return;
  picker.addEventListener('input', function() { text.value = picker.value; });
  text.addEventListener('input', function() {
    var v = text.value.trim();
    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(v)) picker.value = v;
  });
})();

// ── Generic Repeater System ────────────────────────────────────────
(function() {
  function updateLabels(repeater) {
    var items = repeater.querySelectorAll(':scope > .r-item');
    var labelBase = repeater.dataset.labelBase || 'Eintrag';
    items.forEach(function(item, i) {
      var lbl = item.querySelector('.r-label');
      if (lbl) lbl.textContent = labelBase + ' ' + (i + 1);
    });
  }

  // Remove button
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.r-remove');
    if (!btn) return;
    var item = btn.closest('.r-item');
    if (!item) return;
    var repeater = item.closest('.repeater');
    item.remove();
    if (repeater) updateLabels(repeater);
  });

  // Add button
  document.querySelectorAll('.r-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var repeaterId = btn.dataset.repeater;
      if (!repeaterId) return;
      var repeater = document.getElementById(repeaterId);
      if (!repeater) return;

      var fields = [];
      try { fields = JSON.parse(btn.dataset.fields || '[]'); } catch(e) {}

      var item = document.createElement('div');
      item.className = 'r-item';

      var count = repeater.querySelectorAll(':scope > .r-item').length + 1;
      var labelBase = repeater.dataset.labelBase || 'Eintrag';

      var headHtml = '<div class="r-item-head"><span class="r-handle">⠿</span><span class="r-label">' + labelBase + ' ' + count + '</span><button type="button" class="r-remove btn btn-danger btn-xs">✕</button></div>';
      var bodyHtml = '<div class="r-item-body">';

      // Group fields into rows of 2 if same line
      var i = 0;
      while (i < fields.length) {
        var f = fields[i];
        if (f.type === 'textarea') {
          bodyHtml += '<div class="field' + (i > 0 ? ' mt-8' : '') + '">';
          bodyHtml += '<label>' + escHtml(f.label) + '</label>';
          bodyHtml += '<textarea name="' + escHtml(f.name) + '"></textarea>';
          bodyHtml += '</div>';
          i++;
        } else {
          // Check if next field is also text type - pair them
          if (i + 1 < fields.length && fields[i+1].type !== 'textarea') {
            bodyHtml += '<div class="form-row' + (i > 0 ? ' mt-8' : '') + '">';
            bodyHtml += '<div class="field"><label>' + escHtml(f.label) + '</label><input type="text" name="' + escHtml(f.name) + '"></div>';
            var f2 = fields[i+1];
            bodyHtml += '<div class="field"><label>' + escHtml(f2.label) + '</label><input type="text" name="' + escHtml(f2.name) + '"></div>';
            bodyHtml += '</div>';
            i += 2;
          } else {
            bodyHtml += '<div class="field' + (i > 0 ? ' mt-8' : '') + '">';
            bodyHtml += '<label>' + escHtml(f.label) + '</label>';
            bodyHtml += '<input type="text" name="' + escHtml(f.name) + '">';
            bodyHtml += '</div>';
            i++;
          }
        }
      }
      bodyHtml += '</div>';

      item.innerHTML = headHtml + bodyHtml;
      repeater.appendChild(item);
      setupDrag(item);

      // Focus first input
      var first = item.querySelector('input, textarea');
      if (first) first.focus();
    });
  });

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Drag & Drop ──────────────────────────────────────────────────
  var dragSrc = null;

  function setupDrag(item) {
    item.setAttribute('draggable', 'true');
    item.addEventListener('dragstart', function(e) {
      dragSrc = item;
      item.classList.add('is-ghost');
      e.dataTransfer.effectAllowed = 'move';
    });
    item.addEventListener('dragend', function() {
      item.classList.remove('is-ghost');
      document.querySelectorAll('.r-item.is-target').forEach(function(t) { t.classList.remove('is-target'); });
      dragSrc = null;
    });
    item.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      if (dragSrc && dragSrc !== item) {
        item.classList.add('is-target');
      }
    });
    item.addEventListener('dragleave', function() {
      item.classList.remove('is-target');
    });
    item.addEventListener('drop', function(e) {
      e.preventDefault();
      item.classList.remove('is-target');
      if (!dragSrc || dragSrc === item) return;
      var repeater = item.closest('.repeater');
      if (!repeater) return;
      var items = Array.from(repeater.querySelectorAll(':scope > .r-item'));
      var srcIdx = items.indexOf(dragSrc);
      var tgtIdx = items.indexOf(item);
      if (srcIdx < tgtIdx) {
        repeater.insertBefore(dragSrc, item.nextSibling);
      } else {
        repeater.insertBefore(dragSrc, item);
      }
      updateLabels(repeater);
    });

    // Handle click on head to toggle body
    var head = item.querySelector('.r-item-head');
    if (head) {
      head.addEventListener('click', function(e) {
        if (e.target.closest('.r-remove') || e.target.closest('.r-handle')) return;
        var body = item.querySelector('.r-item-body');
        if (body) body.classList.toggle('collapsed');
      });
    }
  }

  document.querySelectorAll('.r-item').forEach(setupDrag);
  document.querySelectorAll('.repeater').forEach(function(r) {
    var tag = r.id || '';
    // Set label base from id
    var bases = {
      'faq-repeater': 'Frage',
      'kpi-repeater': 'KPI',
      'warum360-repeater': 'Karte',
      'cases-repeater': 'Case',
      'testi-repeater': 'Stimme',
      'ablauf-repeater': 'Schritt',
      'vergleich-neg-repeater': 'Punkt',
      'vergleich-pos-repeater': 'Punkt',
    };
    if (bases[tag]) r.dataset.labelBase = bases[tag];
  });
})();
</script>
<?php
$pageContent = ob_get_clean();
$pageTitle = 'Inhalte';
$currentPage = 'content';
$showActionBar = true;
$actionBarFormId = 'main-form';
include dirname(__DIR__) . '/partial/layout.php';
