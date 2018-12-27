<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/slack.php';

// Project name
set('application', 'Makeup');

// Project repository
set('repository', 'git@bitbucket.org:');
set('branch', 'master');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment

set('slack_webhook', '');
set('slack_title', '');
set('slack_text', '_{{user}}_ deploying `{{branch}}` to *{{target}}*');
set('slack_success_text', 'Deploy to *{{target}}* successful');
set('slack_failure_text', 'Deploy to *{{target}}* failed');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', [
    'storage/app/public/pages',
    'storage/app/public/temp',
    'storage/app/public/users',
    'storage/uploads',
    'vendor',
]);

// Hosts
set('default_stage', 'test');
host('')
    ->stage('test')
    ->port(22)
    ->user('')
    ->identityFile('')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->set('deploy_path', '');

host('')
    ->stage('production')
    ->port(22)
    ->user('')
    ->identityFile('')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->set('deploy_path', '');

// Tasks
desc('Prepare environment');
task('files:permissions:artisan', function () {
    run("cd {{release_path}} && chmod +x artisan");
});
after('deploy:shared', 'files:permissions:artisan');

task('files:test_environment', function () {
    run('mv {{release_path}}/.env.test {{release_path}}/.env');
    run('mv {{release_path}}/public/robots.txt.test {{release_path}}/public/robots.txt');
    run('rm {{release_path}}/.env.production');
    run('rm {{release_path}}/public/robots.txt.production');
})->onStage('test');
after('files:permissions:sitemap', 'files:test_environment');

task('files:prod_environment', function () {
    run('mv {{release_path}}/.env.production {{release_path}}/.env');
    run('mv {{release_path}}/public/robots.txt.production {{release_path}}/public/robots.txt');
    run('rm {{release_path}}/.env.test');
    run('rm {{release_path}}/public/robots.txt.test');
})->onStage('production');
after('files:permissions:sitemap', 'files:prod_environment');

desc('Execute artisan route:cache-separate');
task('artisan:route:cache-separate', function () {
    run('{{bin/php}} {{release_path}}/artisan route:cache-separate');
});
after('artisan:config:cache', 'artisan:route:cache-separate');

desc('Restart PHP-FPM service');
task('php-fpm:restart:production', function () {
    run('sudo systemctl restart php7.2-fpm.service');
})->onStage('production');
after('deploy:symlink', 'php-fpm:restart:production');

task('php-fpm:restart:test', function () {
    run('sudo systemctl restart php7.1-fpm.service');
})->onStage('test');
after('deploy:symlink', 'php-fpm:restart:test');

before('deploy:symlink', 'deploy:public_disk');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
