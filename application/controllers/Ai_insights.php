<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AI Insights Controller
 * Uses OpenAI Responses API.
 */
class Ai_insights extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->load->model('Ai_insights_model', 'insight');
    }

    private function dashboard_data(int $months = 12): array
    {
        $pay_rows = $this->insight->get_monthly_payments($months);
        $pay_kpis = $this->insight->get_payment_kpis();
        $pay_by_cat = $this->insight->get_payment_by_category();

        $comp_rows = $this->insight->get_monthly_complaints($months);
        $comp_kpis = $this->insight->get_complaint_kpis();
        $comp_by_cat = $this->insight->get_complaints_by_category();

        $member_kpis = $this->insight->get_member_kpis();
        $member_growth = $this->insight->get_monthly_member_growth($months);
        $occupancy = $this->insight->get_flat_occupancy();

        $visitor_rows = $this->insight->get_monthly_visitors($months);
        $visitor_kpis = $this->insight->get_visitor_kpis();

        $staff_kpis = $this->insight->get_staff_kpis();
        $upcoming_events = $this->insight->get_upcoming_events(6);

        $pay_vals = $this->insight->extract_column($pay_rows, 'collected');
        $pay_periods = $this->insight->extract_periods($pay_rows);
        $pay_labels = $this->insight->extract_labels($pay_rows);

        $comp_vals = $this->insight->extract_column($comp_rows, 'total');
        $comp_periods = $this->insight->extract_periods($comp_rows);
        $comp_labels = $this->insight->extract_labels($comp_rows);

        $visitor_vals = $this->insight->extract_column($visitor_rows, 'total');
        $visitor_periods = $this->insight->extract_periods($visitor_rows);
        $visitor_labels = $this->insight->extract_labels($visitor_rows);

        $member_vals = $this->insight->extract_column($member_growth, 'new_members');
        $member_periods = $this->insight->extract_periods($member_growth);
        $member_labels = $this->insight->extract_labels($member_growth);

        $future_labels = $this->insight->future_labels(3);
        $future_periods = $this->insight->future_periods(3);

        $pay_lr = $this->insight->linear_regression($pay_vals, 3);
        $pay_es = $this->insight->exponential_smoothing($pay_vals, 3);
        $pay_wma = $this->insight->weighted_moving_average($pay_vals, 3);
        $pay_ensemble = $this->insight->ensemble_forecast($pay_vals, $pay_periods, 3);
        $pay_ci = $this->insight->forecast_confidence($pay_vals, $pay_ensemble);
        $pay_anomalies = $this->insight->detect_anomalies($pay_vals);
        $pay_growth = $this->insight->growth_analysis($pay_vals);

        $comp_lr = $this->insight->linear_regression($comp_vals, 3);
        $comp_es = $this->insight->exponential_smoothing($comp_vals, 3);
        $comp_ensemble = $this->insight->ensemble_forecast($comp_vals, $comp_periods, 3);
        $comp_ci = $this->insight->forecast_confidence($comp_vals, $comp_ensemble);
        $comp_anomalies = $this->insight->detect_anomalies($comp_vals);
        $comp_growth = $this->insight->growth_analysis($comp_vals);

        $visitor_ensemble = $this->insight->ensemble_forecast($visitor_vals, $visitor_periods, 3);
        $visitor_ci = $this->insight->forecast_confidence($visitor_vals, $visitor_ensemble);
        $visitor_growth = $this->insight->growth_analysis($visitor_vals);

        $member_ensemble = $this->insight->ensemble_forecast($member_vals, $member_periods, 3);

        $total_pay = ((float) ($pay_kpis['total_collected'] ?? 0)) + ((float) ($pay_kpis['total_pending'] ?? 0));
        $payment_risk = $total_pay > 0
            ? min(100, round(((float) ($pay_kpis['total_pending'] ?? 0) / $total_pay) * 100))
            : 0;

        $complaint_risk = min(100, (int) ($comp_kpis['open_count'] ?? 0) * 4);
        $occupancy_risk = max(0, 100 - (float) ($occupancy['rate'] ?? 0));

        $comp_total = max(1, (int) ($comp_kpis['total'] ?? 0));
        $resolution_rate = round(((int) ($comp_kpis['resolved_count'] ?? 0) / $comp_total) * 100, 1);

        $all_kpis = [
            'payment' => $pay_kpis,
            'complaint' => $comp_kpis,
            'occupancy' => $occupancy,
            'staff' => $staff_kpis,
        ];

        $health_score = $this->insight->compute_health_score($all_kpis);
        $health_label = $health_score >= 80 ? 'Excellent' : ($health_score >= 65 ? 'Good' : ($health_score >= 45 ? 'Fair' : 'Poor'));
        $health_color = $health_score >= 80 ? '#2ecc71' : ($health_score >= 65 ? '#3498db' : ($health_score >= 45 ? '#f39c12' : '#e74c3c'));

        $society_name = $this->session->userdata('society_name') ?? 'Your Society';
        $society_tagline = $this->session->userdata('society_tagline') ?? '';
        $role = $this->session->userdata('role_name') ?? '';
        $society_id = (int) $this->session->userdata('society_id');

        return compact(
            'society_name',
            'society_tagline',
            'role',
            'society_id',
            'pay_rows',
            'comp_rows',
            'visitor_rows',
            'member_growth',
            'pay_by_cat',
            'comp_by_cat',
            'upcoming_events',
            'pay_kpis',
            'comp_kpis',
            'member_kpis',
            'occupancy',
            'visitor_kpis',
            'staff_kpis',
            'pay_vals',
            'pay_labels',
            'pay_periods',
            'comp_vals',
            'comp_labels',
            'comp_periods',
            'visitor_vals',
            'visitor_labels',
            'visitor_periods',
            'member_vals',
            'member_labels',
            'member_periods',
            'future_labels',
            'future_periods',
            'pay_lr',
            'pay_es',
            'pay_wma',
            'pay_ensemble',
            'pay_ci',
            'comp_lr',
            'comp_es',
            'comp_ensemble',
            'comp_ci',
            'visitor_ensemble',
            'visitor_ci',
            'member_ensemble',
            'pay_anomalies',
            'comp_anomalies',
            'pay_growth',
            'comp_growth',
            'visitor_growth',
            'payment_risk',
            'complaint_risk',
            'occupancy_risk',
            'resolution_rate',
            'health_score',
            'health_label',
            'health_color'
        );
    }

    public function index()
    {
        $data = $this->dashboard_data(12);
        $data['activePage'] = 'ai_insights';
        $this->load->view('header.php');
        $this->load->view('ai_insights_view', $data);
    }

    public function refresh_csrf()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function extract_openai_text(array $decoded): ?string
    {
        if (!empty($decoded['output_text']) && is_string($decoded['output_text'])) {
            $text = trim($decoded['output_text']);
            return $text !== '' ? $text : null;
        }

        $parts = [];
        if (!empty($decoded['output']) && is_array($decoded['output'])) {
            foreach ($decoded['output'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                if (($item['type'] ?? '') === 'message' && !empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $chunk) {
                        if (!is_array($chunk)) {
                            continue;
                        }
                        if (($chunk['type'] ?? '') === 'output_text' && isset($chunk['text'])) {
                            $parts[] = (string) $chunk['text'];
                        }
                    }
                }
            }
        }

        $text = trim(implode("\n", array_filter($parts)));
        return $text !== '' ? $text : null;
    }

    private function openai_reply(string $instructions, string $input, int $maxTokens = 800): ?string
    {
        $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : getenv('OPENAI_API_KEY');
        $model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4.1-mini';

        if (empty($apiKey)) {
            log_message('error', 'OPENAI_API_KEY is missing');
            return null;
        }

        if (!function_exists('curl_init')) {
            log_message('error', 'cURL is not enabled');
            return null;
        }

        $payload = [
            'model' => $model,
            'instructions' => $instructions,
            'input' => $input,
            'max_output_tokens' => $maxTokens,
            'temperature' => 0.4,
            'store' => false,
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            log_message('error', 'OpenAI curl_exec failed. errno=' . $errno . ' error=' . $curlErr);
            return null;
        }

        log_message('error', 'OpenAI HTTP CODE: ' . $httpCode);
        log_message('error', 'OpenAI RAW BODY: ' . $response);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            log_message('error', 'OpenAI returned invalid JSON');
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', 'OpenAI error payload: ' . $response);
            return null;
        }

        return $this->extract_openai_text($decoded);
    }

    private function fallback_report(array $d): string
    {
        $pay = (float) ($d['pay_kpis']['total_collected'] ?? 0);
        $pending = (float) ($d['pay_kpis']['total_pending'] ?? 0);
        $open = (int) ($d['comp_kpis']['open_count'] ?? 0);
        $resolved = (int) ($d['comp_kpis']['resolved_count'] ?? 0);
        $occ = (float) ($d['occupancy']['rate'] ?? 0);
        $health = (float) ($d['health_score'] ?? 0);

        $lines = [
            "## Executive Summary",
            "Society Health Score: **{$health}/100**. Driven by payments, complaints, occupancy, and staff activity.",
            "",
            "## Financial Health Analysis",
            "Collected: **₹" . number_format($pay, 0) . "**, Pending: **₹" . number_format($pending, 0) . "**, Default Risk: **{$d['payment_risk']}%**.",
            "",
            "## Occupancy & Member Analysis",
            "Occupancy: **{$occ}%**. Active members: **" . (int) ($d['member_kpis']['active'] ?? 0) . "** of **" . (int) ($d['member_kpis']['total'] ?? 0) . "**.",
            "",
            "## Risk Assessment",
            "Open complaints: **{$open}**, Resolved: **{$resolved}**, Complaint risk: **{$d['complaint_risk']}%**.",
            "",
            "## AI Predictions & Forecast",
        ];

        foreach (($d['future_labels'] ?? []) as $i => $label) {
            $lines[] = "- {$label}: Payments **₹" . number_format((float) ($d['pay_ensemble'][$i] ?? 0), 0)
                . "**, Complaints **" . round((float) ($d['comp_ensemble'][$i] ?? 0))
                . "**, Visitors **" . round((float) ($d['visitor_ensemble'][$i] ?? 0)) . "**";
        }

        $lines[] = "";
        $lines[] = "## Action Items";
        $lines[] = "Prioritize collections, review open complaints, and monitor occupancy trends.";

        return implode("\n", $lines);
    }

    private function fallback_chat_reply(string $message, array $d): string
    {
        $m = strtolower(trim($message));

        $payment = (float) ($d['pay_kpis']['total_collected'] ?? 0);
        $pending = (float) ($d['pay_kpis']['total_pending'] ?? 0);
        $open = (int) ($d['comp_kpis']['open_count'] ?? 0);
        $resolved = (int) ($d['comp_kpis']['resolved_count'] ?? 0);
        $occ = (float) ($d['occupancy']['rate'] ?? 0);
        $health = (float) ($d['health_score'] ?? 0);

        if (strpos($m, 'who are you') !== false || strpos($m, 'who r you') !== false) {
            return "I am your Society AI Assistant. I help you understand payments, complaints, occupancy, growth, risks, and forecasts from your society data.";
        }

        if (strpos($m, 'payment') !== false || strpos($m, 'revenue') !== false || strpos($m, 'collect') !== false) {
            return "**Payments:** Collected **₹" . number_format($payment, 0) . "**, pending **₹" . number_format($pending, 0) . "**. Focus on pending dues first.";
        }

        if (strpos($m, 'complaint') !== false || strpos($m, 'issue') !== false) {
            if ($open === 0) {
                return "**Complaints:** There are **0 open complaints**, which is excellent. Keep it that way by maintaining quick response times, preventive maintenance, and regular follow-up.";
            }
            return "**Complaints:** Open **{$open}**, resolved **{$resolved}**. Reduce resolution time by assigning owners and deadlines.";
        }

        if (strpos($m, 'occupancy') !== false || strpos($m, 'flat') !== false || strpos($m, 'member') !== false) {
            return "**Occupancy:** {$occ}% occupied. Keep vacancy follow-up active and track new member onboarding.";
        }

        if (strpos($m, 'health') !== false || strpos($m, 'score') !== false) {
            return "**Health score:** {$health}/100. Based on payments, complaints, occupancy, and staff activity.";
        }

        if (strpos($m, 'forecast') !== false || strpos($m, 'predict') !== false || strpos($m, 'next') !== false) {
            $p = (float) ($d['pay_ensemble'][0] ?? 0);
            $c = (float) ($d['comp_ensemble'][0] ?? 0);
            $v = (float) ($d['visitor_ensemble'][0] ?? 0);
            return "Next month forecast: payments **₹" . number_format($p, 0) . "**, complaints **" . round($c) . "**, visitors **" . round($v) . "**.";
        }

        return "I have reviewed the current data snapshot. Health score is **{$health}/100** and occupancy is **{$occ}%**. Ask about payments, complaints, occupancy, forecasts, or who I am.";
    }

    private function build_snapshot_text(array $d): string
    {
        $lines = [];

        $lines[] = "SOCIETY SNAPSHOT";
        $lines[] = "Society: " . ($d['society_name'] ?? 'Society');
        $lines[] = "Health Score: " . ($d['health_score'] ?? 0) . "/100 (" . ($d['health_label'] ?? 'Unknown') . ")";
        $lines[] = "Payments Collected: ₹" . number_format((float) ($d['pay_kpis']['total_collected'] ?? 0), 0);
        $lines[] = "Payments Pending: ₹" . number_format((float) ($d['pay_kpis']['total_pending'] ?? 0), 0);
        $lines[] = "Open Complaints: " . (int) ($d['comp_kpis']['open_count'] ?? 0);
        $lines[] = "Resolved Complaints: " . (int) ($d['comp_kpis']['resolved_count'] ?? 0);
        $lines[] = "Occupancy: " . (float) ($d['occupancy']['rate'] ?? 0) . "%";
        $lines[] = "Members: " . (int) ($d['member_kpis']['total'] ?? 0) . " total, " . (int) ($d['member_kpis']['active'] ?? 0) . " active";
        $lines[] = "Visitors This Month: " . (int) ($d['visitor_kpis']['this_month'] ?? 0);
        $lines[] = "Today Visitors: " . (int) ($d['visitor_kpis']['today'] ?? 0);
        $lines[] = "Payment Risk: " . (int) ($d['payment_risk'] ?? 0) . "%";
        $lines[] = "Complaint Risk: " . (int) ($d['complaint_risk'] ?? 0) . "%";
        $lines[] = "Occupancy Risk: " . (int) ($d['occupancy_risk'] ?? 0) . "%";
        $lines[] = "";
        $lines[] = "FORECASTS (next 3 months)";
        foreach (($d['future_labels'] ?? []) as $i => $label) {
            $lines[] = "- {$label}: Payments ₹" . number_format((float) ($d['pay_ensemble'][$i] ?? 0), 0)
                . ", Complaints " . round((float) ($d['comp_ensemble'][$i] ?? 0))
                . ", Visitors " . round((float) ($d['visitor_ensemble'][$i] ?? 0));
        }
        $lines[] = "";
        $lines[] = "RECENT CHART TRENDS";
        $lines[] = "- Payments growth avg: " . (float) ($d['pay_growth']['avg_growth'] ?? 0) . "%";
        $lines[] = "- Complaints growth avg: " . (float) ($d['comp_growth']['avg_growth'] ?? 0) . "%";
        $lines[] = "- Visitors growth avg: " . (float) ($d['visitor_growth']['avg_growth'] ?? 0) . "%";

        return implode("\n", $lines);
    }

    private function build_chat_instructions(array $d): string
    {
        return
            "You are the AI assistant for a residential society dashboard.\n" .
            "Answer the user's exact question directly and specifically.\n" .
            "Do not give canned or fixed template answers.\n" .
            "Use the live snapshot and recent conversation context.\n" .
            "If the user asks 'who are you', introduce yourself as the society AI assistant.\n" .
            "If the user asks for reduction of complaints and open complaints are 0, say there are no open complaints and give prevention advice instead.\n" .
            "If a number is low or high, interpret it before suggesting action.\n" .
            "Be practical, concise, and grounded in the provided data.\n" .
            "Use Indian currency with ₹ and write in natural language.\n" .
            "If data is missing, say what is missing and still answer as helpfully as possible.\n" .
            "Keep the answer focused on the asked question, not a generic summary.\n";
    }

    private function build_report_instructions(array $d): string
    {
        return
            "You are an expert residential society analyst.\n" .
            "Write a useful markdown report with clear headings.\n" .
            "Use the live metrics and forecasts provided.\n" .
            "Be practical, specific, and avoid vague filler.\n";
    }

    public function generate_report()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->dashboard_data(12);

        $prompt = $this->build_snapshot_text($data) . "\n\n" .
            "TASK:\nGenerate a practical residential society report for \"" . ($data['society_name'] ?? 'Society') . "\".\n" .
            "Write sections for executive summary, financial health, occupancy, risk assessment, predictions, and action items.";

        $report = $this->openai_reply($this->build_report_instructions($data), $prompt, 1500);
        if (!$report) {
            $report = $this->fallback_report($data);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'report' => $report,
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function chat()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $message = trim((string) $this->input->post('message', true));
        $historyRaw = $this->input->post('history', false);

        if ($message === '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'error' => 'Empty message'
                ]));
            return;
        }

        $history = [];
        if (is_string($historyRaw) && $historyRaw !== '') {
            $decoded = json_decode($historyRaw, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }

        $data = $this->dashboard_data(12);

        $system = $this->build_chat_instructions($data);

        $prompt = "Live society data:\n"
            . "- Society: " . ($data['society_name'] ?? 'Society') . "\n"
            . "- Health score: " . ($data['health_score'] ?? 0) . "/100\n"
            . "- Payments collected: ₹" . number_format((float) ($data['pay_kpis']['total_collected'] ?? 0), 0) . "\n"
            . "- Payments pending: ₹" . number_format((float) ($data['pay_kpis']['total_pending'] ?? 0), 0) . "\n"
            . "- Open complaints: " . (int) ($data['comp_kpis']['open_count'] ?? 0) . "\n"
            . "- Resolved complaints: " . (int) ($data['comp_kpis']['resolved_count'] ?? 0) . "\n"
            . "- Occupancy: " . (float) ($data['occupancy']['rate'] ?? 0) . "%\n"
            . "- Active members: " . (int) ($data['member_kpis']['active'] ?? 0) . "\n\n";

        if (!empty($history)) {
            $prompt .= "Recent conversation:\n";
            foreach (array_slice($history, -8) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $role = $item['role'] ?? '';
                $content = trim((string) ($item['content'] ?? ''));
                if ($content !== '' && in_array($role, ['user', 'assistant'], true)) {
                    $prompt .= strtoupper($role) . ': ' . $content . "\n";
                }
            }
            $prompt .= "\n";
        }

        $prompt .= "User question: " . $message . "\n";
        $prompt .= "Answer now in a helpful, natural way.";

        $reply = $this->openai_reply($system, $prompt, 800);

        if (!$reply) {
            $reply = $this->fallback_chat_reply($message, $data);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'reply' => $reply,
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
