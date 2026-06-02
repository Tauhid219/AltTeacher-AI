<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lesson Prep Packet - Booking #{{ $booking->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #28a745;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #6c757d;
            font-size: 14px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 13px;
        }
        .meta-table td.label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 25%;
        }
        .section-title {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            font-size: 15px;
            margin-top: 25px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .summary-text {
            font-size: 13px;
            text-align: justify;
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #28a745;
            border-radius: 4px;
        }
        .quiz-item {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
            page-break-inside: avoid;
        }
        .quiz-question {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .quiz-options {
            font-size: 12px;
            margin-left: 15px;
        }
        .quiz-options li {
            list-style-type: none;
            margin-bottom: 3px;
        }
        .quiz-answer {
            color: #28a745;
            font-weight: bold;
            font-size: 11px;
            margin-top: 4px;
            margin-left: 15px;
        }
        .icebreaker-item {
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            font-size: 13px;
            page-break-inside: avoid;
        }
        .icebreaker-title {
            font-weight: bold;
            color: #198754;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>AltTeacher-AI</h1>
        <p>Substitute Classroom Prep & Lesson Continuity Packet</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Subject:</td>
            <td>{{ $booking->substituteJob->subject }}</td>
            <td class="label">Grade Level:</td>
            <td>{{ $booking->substituteJob->grade_level }}</td>
        </tr>
        <tr>
            <td class="label">Date:</td>
            <td>{{ \Carbon\Carbon::parse($booking->substituteJob->date)->format('F d, Y') }}</td>
            <td class="label">Class Hours:</td>
            <td>{{ \Carbon\Carbon::parse($booking->substituteJob->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->substituteJob->end_time)->format('h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">School Name:</td>
            <td>{{ $booking->substituteJob->schoolProfile->school_name }}</td>
            <td class="label">Address:</td>
            <td>{{ $booking->substituteJob->schoolProfile->address }}</td>
        </tr>
        <tr>
            <td class="label">Teacher Name:</td>
            <td colspan="3">{{ auth()->user()->name }}</td>
        </tr>
    </table>

    <div class="section-title">I. AI Adapted Lesson Summary</div>
    <div class="summary-text">
        {{ $lessonPlan->ai_summary ?: 'No summary generated.' }}
    </div>

    <div style="page-break-before: always;"></div>

    <div class="section-title">II. Quick Icebreaker Activities (3 Games)</div>
    @php
        $icebreakers = $lessonPlan->ai_generated_activities['icebreakers'] ?? [];
    @endphp
    @forelse($icebreakers as $index => $game)
        <div class="icebreaker-item">
            <div class="icebreaker-title">Activity {{ $index + 1 }}</div>
            <div>{{ $game }}</div>
        </div>
    @empty
        <p style="font-size: 13px; color: #6c757d; font-style: italic;">No icebreaker activities generated.</p>
    @endforelse

    <div class="section-title">III. Lesson Continuity Quiz (10 Questions)</div>
    @php
        $quizzes = $lessonPlan->ai_generated_activities['quizzes'] ?? [];
    @endphp
    @forelse($quizzes as $index => $quiz)
        <div class="quiz-item">
            <div class="quiz-question">{{ $index + 1 }}. {{ $quiz['question'] ?? '' }}</div>
            <ul class="quiz-options">
                @php
                    $options = $quiz['options'] ?? [];
                    $labels = ['A', 'B', 'C', 'D'];
                @endphp
                @foreach($options as $optIndex => $option)
                    <li><strong>{{ $labels[$optIndex] ?? '' }}.</strong> {{ $option }}</li>
                @endforeach
            </ul>
            <div class="quiz-answer">Correct Answer: {{ $quiz['answer'] ?? 'N/A' }}</div>
        </div>
    @empty
        <p style="font-size: 13px; color: #6c757d; font-style: italic;">No quiz questions generated.</p>
    @endforelse

</body>
</html>
