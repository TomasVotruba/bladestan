@include('partials.filename1', ['vehicle' => 'truck'])
@include('partials.filename2', ['animal' => 'frogs'])
-----
<?php

/** file: foo.blade.php, line: 1 */
function () {
    $vehicle = 'truck';
};
/** file: foo.blade.php, line: 2 */
function () {
    $animal = 'frogs';
};
