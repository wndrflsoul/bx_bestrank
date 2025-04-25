<?php
use Bitrix\Main\Loader;
include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

\Hawk\HawkCatcher::instance('eyJpbnRlZ3JhdGlvbklkIjoiZjcwNjY4OWEtYTJmYS00Y2M2LWJlYzgtNGZlODkxYmMzNGI5Iiwic2VjcmV0IjoiZmIwNjYzZDQtZjM1Yi00YjQ0LTg1ODAtNjcwZTZkOGY4MWZhIn0=');
\Hawk\HawkCatcher::enableHandlers(true, true, true);