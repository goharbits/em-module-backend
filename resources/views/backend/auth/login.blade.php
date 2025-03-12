<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <meta name="color-scheme" content="dark light" />
    <title>Login | Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="{{ 'assets/css/main.css' }}" />
    <link rel="stylesheet" type="text/css" href="{{ 'assets/css/utilities.css' }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet"
        href="{{ 'assets/cdn.jsdelivr.net/npm/bootstrap-icons%401.7.2/font/bootstrap-icons.css' }}" />
    <script defer="defer" data-domain="webpixels.works" src="{{ 'assets/plausible.io/js/script.js' }}"></script>
</head>

<body>
    <div>
        <div class="px-5 py-5 p-lg-0 h-screen bg-surface-secondary d-flex flex-column justify-content-center">
            <div class="d-flex justify-content-center">
                <div
                    class="col-12 col-md-9 col-lg-6 min-h-lg-screen d-flex flex-column justify-content-center py-lg-16 px-lg-20 position-relative">
                    <div class="row">
                        <div class="col-lg-10 col-md-9 col-xl-7 mx-auto">
                            <div class="text-center mb-12">
                                <h3 class="display-5">👋</h3>
                                <h1 class="ls-tight font-bolder mt-6">Admin panel!</h1>
                                <p class="mt-2">Manage everything going on your system</p>
                            </div>
                            <form>
                                <div class="mb-5">
                                    <label class="form-label text-white" for="email">Email address</label>
                                    <input type="email" class="form-control" id="email"
                                        placeholder="Your email address" />
                                </div>
                                <div class="mb-5">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <label class="form-label" for="password">Password</label>
                                        </div>

                                    </div>
                                    <input type="password" class="form-control" id="password" placeholder="Password"
                                        autocomplete="current-password" />
                                </div>
                                <div>
                                    <a href="#" class="btn btn-primary w-full">Sign in</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ 'assets/js/main.js' }}"></script>
</body>

</html>
