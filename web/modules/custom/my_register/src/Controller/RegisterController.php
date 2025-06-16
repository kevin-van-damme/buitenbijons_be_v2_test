<?php

namespace Drupal\my_register\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;

class RegisterController
{
    public function register(Request $request)
    {
        $email = $request->get('email');
        $username = $request->get('username');
        $password = $request->get('password');
        $verifyPassword = $request->get('verifyPassword');

        // Basic validation
        if (empty($email) || empty($username) || empty($password) || empty($verifyPassword)) {
            return new JsonResponse([
                'type' => 'errorFields',
                'message' => 'All fields are required.',
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'type' => 'errorValidEmail',
                'message' => 'Invalid email address.',
            ], 400);
        }

        if (strlen($username) < 3) {
            return new JsonResponse([
                'type' => 'errorUsernameMinLength',
                'message' => 'Username too short.',
            ], 400);
        }

        if (strlen($username) > 20) {
            return new JsonResponse([
                'type' => 'errorUsernameMaxLength',
                'message' => 'Username too long.',
            ], 400);
        }

        if (strlen($password) < 6) {
            return new JsonResponse([
                'type' => 'errorPasswordMinLength',
                'message' => 'Password too short.',
            ], 400);
        }

        if ($password !== $verifyPassword) {
            return new JsonResponse([
                'type' => 'errorPasswordMatch',
                'message' => 'Passwords do not match.',
            ], 400);
        }

        // ✅ Check if email already exists
        $existing_users_email = \Drupal::entityTypeManager()
            ->getStorage('user')
            ->getQuery()
            ->condition('mail', $email)
            ->execute();

        if (!empty($existing_users_email)) {
            return new JsonResponse([
                'type' => 'errorEmail',
                'message' => 'Email already registered.',
            ], 409);
        }

        // ✅ Check if username already exists
        $existing_users_name = \Drupal::entityTypeManager()
            ->getStorage('user')
            ->getQuery()
            ->condition('name', $username)
            ->execute();

        if (!empty($existing_users_name)) {
            return new JsonResponse([
                'type' => 'errorValidUsername',
                'message' => 'Username already taken.',
            ], 409);
        }

        // ✅ Create the user
        $user = User::create([
            'name' => $username,
            'mail' => $email,
            'pass' => $password,
            'status' => 1,
        ]);
        $user->enforceIsNew();
        $user->save();

        return new JsonResponse([
            'type' => 'success',
            'message' => 'Account created successfully.',
            'status' => 201,
        ]);
    }
}
