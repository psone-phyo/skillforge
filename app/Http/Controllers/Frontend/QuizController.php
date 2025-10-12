<?php
namespace App\Http\Controllers\Frontend;

use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController{
    public function get($id){
        $data = Quiz::with('quizQuestions.options')->where('course_id', $id)->first();
        return response()->json($data);
    }

    public function submit(Request $request){
        $data = $request->all();
        $quiz = Quiz::with(['quizQuestions.options','course'])->find($data['quiz_id']);
            $totalScore = 0;

        foreach ($data['answers'] as $questionId => $answerId) {
            $option = QuizQuestionOption::where('quiz_question_id', $questionId)
                ->where('id', $answerId)
                ->first();

            if ($option && $option->is_correct) {
                $question = QuizQuestion::find($questionId);
                $totalScore += $question->point;
            }
        }

        $quizAttempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $data['quiz_id'],
            'score' => $totalScore,
        ]);

        if ($totalScore >= $quiz->passing_score){
            Certificate::create([
                'course_id' => $quiz->course->id,
                'user_id' => Auth::id(),
                'issue_number' => 'SF-' . str_pad($quizAttempt->id, 6, '0', STR_PAD_LEFT),
                'issue_date' => now()->format('Y-m-d'),
                'certificate_url' => config('app.url')."/get/certificate/"
            ]);
        }

        return response()->json([
            'score' => $totalScore,
            'passed' => $quiz->passing_score,
        ]);
    }
}
