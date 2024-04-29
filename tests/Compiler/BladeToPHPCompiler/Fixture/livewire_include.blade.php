<livewire:component :b="$b" c="{{$c}}"/>
-----
<?php

/** file: foo.blade.php, line: 1 */
echo Illuminate\View\AnonymousComponent::resolve(['b' => $b, 'c' => '' . e($c) . ''])->render();
