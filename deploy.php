<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@gitlab.junglesafariindia.in:abhishek.sinha/hrm-system.git');
set('keep_releases', 3); // Keep only 4 releases

add('shared_files', []);

// Host configuration
host('production')
    ->set('hostname', '13.232.130.57')
    ->set('remote_user', 'ubuntu')
    ->set('deploy_path', '/var/www/html/hrm-system4')
    ->set('identity_file', '/home/ubuntu/.ssh/id_ed25519');  // Updated path to correct identity file

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:lock',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'deploy:unlock',
    'deploy:symlink',
    'deploy:cleanup',
    'deploy:optimize_clear'
]);

task('deploy:optimize_clear', function () {
    run('cd {{release_path}} && php artisan optimize:clear');
});

task('deploy:lock', function () {
    run('echo "ci" > {{deploy_path}}/.dep/deploy.lock');
});

task('deploy:unlock', function () {
    run('rm -f {{deploy_path}}/deployer.lock');
});

task('deploy:symlink', function () {
    run('cd {{deploy_path}} && ln -sfn {{release_path}} current');
});

// Task to create symbolic links


after('deploy:failed', 'deploy:unlock');
