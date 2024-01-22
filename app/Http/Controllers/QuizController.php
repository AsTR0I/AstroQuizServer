<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;

class QuizController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function store(Request $request)
    {
                    // !!!!!!!!!!!!!!!
            // {
            //     "title": "Название опроса",
            //     "questions": [
            //         {
            //             "text": "Текст вопроса 1",
            //             "answers": [
            //                 {
            //                     "text":"Овет 1",
            //                     "is_correct":true
            //                 },
            // !!!!!!!!!!!!!!!!!
            // Сохранение данных опроса в таблицу quizzes
        $user = User::where('access_token',$request -> cookie())->first();

        if ($user){
            // Получение данных из POST-запроса
            $quizData = $request->all();

            $uniqueCode = uniqid('quiz_');
            $quiz = Quiz::create([
                'user_id'=>$user -> id,
                'title' => $quizData['title'],
                'unique_code'=>$uniqueCode
            ]);
            // Сохранение данных вопросов и ответов в соответствующие таблицы
            foreach ($quizData['questions'] as $question) {
                $newQuestion = Question::create([
                    'text' => $question['text'],
                    'quiz_id' => $quiz->id
                ]);
                foreach ($question['answers'] as $answer) {
                        Answer::create([
                            'text' => $answer['text'],
                            'is_correct'=> $answer['is_correct'],
                            'question_id' => $newQuestion->id,
                            'user_id' => $user -> id
                        ]);
                    
                }
            }
            // Возвращаем успешный ответ
            return response()->json(
                [
                    'message' => 'Данные успешно сохранены',
                    "quiz" => $quiz
                ]
                , 201);
        }
    }

    public function getQuiz(Request $request, $unique_code)
    {
        $user = User::where('access_token',$cookie = $request -> cookie())->first();
        $quiz = Quiz::where('unique_code',$unique_code) -> first();

        $questions = Question::where('quiz_id', $quiz->id)->get();


        $questionsArray = [];
        foreach ($questions as $questions) {
            $answers = Answer::where('question_id',$questions->id)->get();
            $questionsArray[] = [
                "text" => $questions->text,
                "answers" => $answers
            ];
        }

        return response() -> json([
            'title' => $quiz -> title,
            "question" => $questionsArray
        ]);
    }
}


