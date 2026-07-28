<?php
$file = 'resources/views/profile/partials/update-profile-information-form.blade.php';
$content = file_get_contents($file);

$content = preg_replace(
    '/(editing:\s*false,)/',
    "$1\n        completed: {{ \$user->profile_completed ? 'true' : 'false' }},",
    $content
);

$content = str_replace(':disabled="!editing"', ':disabled="!editing || completed"', $content);
$content = str_replace('name="semestre" :disabled="!editing || completed"', 'name="semestre" :disabled="!editing"', $content);
$content = str_replace('name="horario_file" type="file" :disabled="!editing || completed"', 'name="horario_file" type="file" :disabled="!editing"', $content);

file_put_contents($file, $content);
echo "Updated profile view.\n";
