<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Authorization</title>
    <!-- Styles -->
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-50 dark:bg-gray-900 passport-authorize">

    <main class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0 overflow">
        <div
            class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-lg xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8 text-gray-900 dark:text-white">
                <h1 class="text-xl text-center font-bold leading-tight tracking-tight md:text-2xl ">
                    Authorization Request
                </h1>
				<div>
					<p class="text-center text-lg"><strong>{{ $client->name }}</strong> is requesting permission to access your account.</p>
					 @if (count($scopes) > 0)
						<div class="scopes my-8">
							<p><strong>This application will be able to:</strong></p>
							<ul class="list-disc list-inside">
								@foreach ($scopes as $scope)
									<li>{{ $scope->description }}</li>
								@endforeach
							</ul>
						</div>
					@endif
				</div>
				
				<div class="grid grid-cols-2 gap-3 text-center">
					<div>
						<form method="post" action="{{ route('passport.authorizations.approve') }}">
							@csrf

							<input type="hidden" name="state" value="{{ $request->state }}">
							<input type="hidden" name="client_id" value="{{ $client->getKey() }}">
							<input type="hidden" name="auth_token" value="{{ $authToken }}">
							<button type="submit" class="block w-full focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Authorize</button>
						</form>
					</div>
					<div>
						<form method="post" action="{{ route('passport.authorizations.deny') }}">
							@csrf
							@method('DELETE')
							<input type="hidden" name="state" value="{{ $request->state }}">
							<input type="hidden" name="client_id" value="{{ $client->getKey() }}">
							<input type="hidden" name="auth_token" value="{{ $authToken }}">
							<button type="submit" class="block w-full focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Cancel</button>
						</form>
					</div>
				</div>
            </div>
        </div>
    </main>
</body>

</html>
