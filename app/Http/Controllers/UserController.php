<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    // $user = User::where('login',$login)->first();
    // if(!$user){
    //    return response() -> json([
    //     'error' => 'login isn`t correct'
    //    ]); 
    // }

    function allUsers(Request $request) {
        // Получаем куки из запроса
        $cookie = $request->cookie('token');
    
        if($cookie){
            // Поиск пользователя по токену доступа
            $user = User::where('access_token', $cookie)->first();
            
            // Проверка наличия пользователя с указанным токеном
            if($user){
                // Получаем всех пользователей
                $users = User::all();
                return response()->json($users);
            } else {
                // Возвращаем сообщение об ошибке, если пользователь не найден
                return response()->json([
                    'error' => 'You are not authorized'
                ]);
            }
        } else {
            // Возвращаем сообщение об ошибке, если отсутствует токен доступа
            return response()->json([
                'error' => 'Token is not provided'
            ]);
        }
    }
    

    function registration(Request $request) {
        $valdator = Validator::make($request -> all(),[
            'name'=>'required|min:4',
            'login'=>'required|min:5',
            'email'=>'required|email',
            'password'=>'required|min:6',
        ]);

        if($valdator -> fails()){
            return response() -> json([
                'error' => $valdator -> messages(),
                200
            ]);
        }

        $login = $request->input('login');
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role_default = '333';

        if (User::where('login',$login)->first()){
            return response() -> json([
                'error' => 'This login already exists'
            ]);
        }
        else {
            if (User::where('email',$email)->first()){
                return response() -> json([
                    'error' => 'This email already exists'
                ]);
            }
            else{
                User::create([
                    'login'=>$login,
                    'name'=>$name,
                    'email'=>$email,
                    'role_code'=>$role_default,
                    'password'=>bcrypt($password),
                ]);

                return response()->json([
                    'message' => 'User registered successfully',
                ]);
            }
            }
        }

        // login
    public function login(Request $request){
    // Валидация входных данных
    $validator = Validator::make($request->all(), [
        'login' => 'required|min:5',
        'password' => 'required|min:5',
    ]);
    if ($validator->fails()) {
        // Возвращаем сообщение об ошибке в формате JSON, если валидация не удалась
        return response()->json([
            'error' => $validator->messages()
        ]);
    }

    // Извлечение логина и пароля из запроса
    $login = $request->input('login');
    $password = $request->input('password');

    // Поиск пользователя по логину
    $user = User::where('login', $login)->first();
    if (!$user) {
        // Возвращаем сообщение об ошибке, если пользователь с указанным логином не найден
        return response()->json([
            'error' => 'Login isn\'t correct'
        ]);
    } else {
        // Проверка правильности пароля
        if (Hash::check($password, $user->password)) {
            // Находим роль пользователя по его коду
            $role = Role::where('code', $user->role_code)->first();

            // Парсим строковое представление даты/времени в объект Carbon
            $expiresAt = Carbon::parse($user->access_token_expires_at);

            // Проверка срока действия токена доступа
            if ($user->access_token_expires_at && $expiresAt > now()) {
                // Если токен еще активен, возвращаем данные пользователя и устанавливаем куку
                $response = response()->json([
                    'user' => [
                        'name' => $user->name,
                        'access_token' =>$user->access_token,
                        'role' => $role->name,
                    ]
                ]);
                $response->headers->setCookie(cookie('token', $user->access_token, strtotime($user->access_token_expires_at)));
                return $response;
            } else {
                // Генерация нового токена доступа, если текущий токен недействителен или истек
                if (!$token = auth()->attempt($request->only('login', 'password'))) {
                    // Возвращаем ошибку "Unauthorized" в случае неудачной аутентификации
                    return response()->json([
                        'error' => 'Unauthorized',
                    ], 401);
                }

                // Создание нового токена доступа и установка его в куку
                $response = $this->createNewAccessToken($token, $user, $role);

                // Установка срока жизни токена в базе данных
                $user->access_token_expires_at = now()->addMinutes(config('jwt.ttl') * 60);
                $user->access_token = $token; // Обновление токена в базе данных
                $user->save();

                $response->headers->setCookie(cookie('token', $token, config('jwt.ttl') * 60));
                return $response;
            }
        } else {
            // Возвращаем ошибку "Password isn't correct", если пароль неверный
            return response()->json([
                'error' => 'Password isn\'t correct'
            ]);
        }
    }
    }
        
        private function createNewAccessToken($token, $user, $role){
            // Получаем время жизни токена из конфигурации, по умолчанию 60 минут
            $ttl = config('jwt.ttl', 60);
            
            // Устанавливаем сгенерированный токен в качестве access_token для пользователя и сохраняем изменения
            $token = $user->access_token;
            $user->save();
        
            // Формируем JSON-ответ с информацией о пользователе и его роли
            return response()->json([
                'user' => [
                    'name' => $user->name,
                    'access_token' =>$user->access_token,
                    'role' => $role->name,
                ]
            ])->withCookie(
                // Устанавливаем куку для токена с указанием имени, значения и времени жизни
                cookie('token', $token, $ttl * 60)
            );
        }

}