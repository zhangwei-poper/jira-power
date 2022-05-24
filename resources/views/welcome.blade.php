<!doctype html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Generate daily report from JIRA work logs</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <!-- Favicons -->
    <meta name="theme-color" content="#7952b3">


    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        main > .container {
            padding: 60px 15px 0;
        }

        pre {
            background-color: #eee;
            padding: 20px;
            border-radius: 20px;
        }

        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;

            font-size: .9rem;
            padding: .15rem;
            background-color: #828282;
            color: #1e1e1e;
            border: ridge 1px #7b7b7c;
            border-radius: 5px;
            text-shadow: #c4c4c4 0 0 2px;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

<header>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">JIRA Power</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Begin page content -->
<main class="flex-shrink-0">
    <div class="container">
        <h1 class="mt-5">Generate daily report from JIRA work logs</h1>
        <div class="row justify-content-md-center">
            <form class="form" method="post">
                {{ csrf_field() }}
                <div class="mb-3">
                    <label for="jira_host" class="form-label">JIRA Host</label>
                    <input type="url" class="form-control" id="jira_host" name="jira_host"
                           placeholder="https://company_name.atlassian.net"
                           value="{{ $defaults['jira_host'] }}">
                </div>
                <div class="mb-3">
                    <label for="jira_user" class="form-label">Your account's email address</label>
                    <input type="text" class="form-control" id="jira_user" name="jira_user"
                           placeholder="tom@jerry.com"
                           value="{{ $defaults['jira_user'] }}">
                </div>
                <div class="mb-3">
                    <label for="jira_pass" class="form-label">Your account's token</label>
                    <input type="text" class="form-control" id="jira_pass" name="jira_pass"
                           placeholder="xxx"
                           value="{{ $defaults['jira_pass'] }}">
                    <div class="form-text">
                        Visit <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank">API Tokens</a>
                        create new api token.
                    </div>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary mb-3">Generate</button>
                </div>
            </form>
        </div>

        <hr>

        @if(isset($error))
            <div class="alert alert-danger" role="alert">
                {{ $error }}
            </div>
        @endif

        @if(isset($text))
            <pre><code>{{ $text }}</code></pre>
        @endif

    </div>
</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <span class="text-muted">^_^</span>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</body>
</html>
