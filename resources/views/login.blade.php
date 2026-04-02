<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>
</head>

<body class="bg-gray-100">

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">

    <!-- Title -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Sign in to your account
        </h2>

        <p class="mt-2 text-center text-sm text-gray-600 max-w-md mx-auto">
            Or
            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                create an account
            </a>
        </p>
    </div>

    <!-- Form -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

            {{-- ERROR --}}
            @if(session('error'))
                <div class="mb-4 text-red-500 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM --}}
                @csrf

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <input name="email" type="email" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Enter your email">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input name="password" type="password" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Enter your password">
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-900">Remember me</span>
                    </label>

                    <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                        Forgot password?
                    </a>
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Sign in
                </button>
            </form>

            <!-- Social -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Or continue with
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-3">
                    <a href="#" class="flex justify-center p-3 border rounded-md">
                        <img class="h-5 w-5" src="https://www.svgrepo.com/show/512120/facebook-176.svg">
                    </a>
                    <a href="#" class="flex justify-center p-3 border rounded-md">
                        <img class="h-5 w-5" src="https://www.svgrepo.com/show/513008/twitter-154.svg">
                    </a>
                    <a href="#" class="flex justify-center p-3 border rounded-md">
                        <img class="h-5 w-5" src="https://www.svgrepo.com/show/506498/google.svg">
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>