module.exports = function (grunt) {
    require('time-grunt')(grunt);
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Копируем файлы из исходников
        copy: {
            distributive: {
                files: [
                    {
                        expand: true,
                        src: [
                            '**/**',
                            '.htaccess',
                            'files/.htaccess',

                            '!files/cache/**/*',
                            'files/cache/.htaccess',
                            '!files/downloads/files/**/*',
                            'files/downloads/files/index.php',

                            '!system/config/autoload/database.local.php',
                            '!system/config/autoload/system.local.php',

                            '!system/vendor/bin/**',

                            '!system/vendor/container-interop/container-interop/docs/**',
                            '!system/vendor/container-interop/container-interop/composer.json',

                            '!system/vendor/erusev/parsedown/test/**',
                            '!system/vendor/erusev/parsedown/composer.json',
                            '!system/vendor/erusev/parsedown/phpunit.xml.dist',

                            '!system/vendor/geshi/geshi/src/geshi/*',
                            'system/vendor/geshi/geshi/src/geshi/css.php',
                            'system/vendor/geshi/geshi/src/geshi/html5.php',
                            'system/vendor/geshi/geshi/src/geshi/javascript.php',
                            'system/vendor/geshi/geshi/src/geshi/php.php',
                            'system/vendor/geshi/geshi/src/geshi/sql.php',
                            'system/vendor/geshi/geshi/src/geshi/xml.php',
                            '!system/vendor/geshi/geshi/contrib/**',
                            '!system/vendor/geshi/geshi/docs/**',
                            '!system/vendor/geshi/geshi/tests/**',
                            '!system/vendor/geshi/geshi/BUGS',
                            '!system/vendor/geshi/geshi/CHANGELOG',
                            '!system/vendor/geshi/geshi/build.properties.dist',
                            '!system/vendor/geshi/geshi/build.xml',
                            '!system/vendor/geshi/geshi/phpunit.xml',
                            '!system/vendor/geshi/geshi/composer.json',

                            '!system/vendor/klein/klein/tests/**',
                            '!system/vendor/klein/klein/composer.json',
                            '!system/vendor/klein/klein/CHANGELOG.md',
                            '!system/vendor/klein/klein/CONTRIBUTING.md',
                            '!system/vendor/klein/klein/phpdoc.dist.xml',
                            '!system/vendor/klein/klein/phpunit.xml.dist',
                            '!system/vendor/klein/klein/UPGRADING.md',

                            '!system/vendor/verot/class.upload.php/test/**',
                            '!system/vendor/verot/class.upload.php/composer.json',

                            '!system/vendor/zendframework/zend-i18n/doc/**',
                            '!system/vendor/zendframework/zend-i18n/CHANGELOG.md',
                            '!system/vendor/zendframework/zend-i18n/composer.json',
                            '!system/vendor/zendframework/zend-i18n/CONDUCT.md',
                            '!system/vendor/zendframework/zend-i18n/CONTRIBUTING.md',
                            '!system/vendor/zendframework/zend-i18n/mkdocs.yml',
                            '!system/vendor/zendframework/zend-i18n/phpcs.xml',

                            '!system/vendor/zendframework/zend-servicemanager/benchmarks/**',
                            '!system/vendor/zendframework/zend-servicemanager/bin/**',
                            '!system/vendor/zendframework/zend-servicemanager/doc/**',
                            '!system/vendor/zendframework/zend-servicemanager/CHANGELOG.md',
                            '!system/vendor/zendframework/zend-servicemanager/CONDUCT.md',
                            '!system/vendor/zendframework/zend-servicemanager/CONTRIBUTING.md',
                            '!system/vendor/zendframework/zend-servicemanager/composer.json',
                            '!system/vendor/zendframework/zend-servicemanager/phpbench.json',
                            '!system/vendor/zendframework/zend-servicemanager/phpcs.xml',
                            '!system/vendor/zendframework/zend-servicemanager/mkdocs.yml',

                            '!system/vendor/zendframework/zend-stdlib/benchmark/**',
                            '!system/vendor/zendframework/zend-stdlib/doc/**',
                            '!system/vendor/zendframework/zend-stdlib/CHANGELOG.md',
                            '!system/vendor/zendframework/zend-stdlib/CONDUCT.md',
                            '!system/vendor/zendframework/zend-stdlib/CONTRIBUTING.md',
                            '!system/vendor/zendframework/zend-stdlib/composer.json',
                            '!system/vendor/zendframework/zend-stdlib/mkdocs.yml',
                            '!system/vendor/zendframework/zend-stdlib/phpcs.xml',

                            '!dist/**',
                            '!distributive/**',
                            '!node_modules/**',
                            '!Gruntfile.js',
                            '!package.json',
                            '!composer.*'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_ar: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/ar/**',
                            'modules/album/locale/ar/**',
                            'modules/downloads/locale/ar/**',
                            'modules/forum/locale/ar/**',
                            'modules/guestbook/locale/ar/**',
                            'modules/help/locale/ar/**',
                            'modules/library/locale/ar/**',
                            'modules/mail/locale/ar/**',
                            'modules/news/locale/ar/**',
                            'modules/profile/locale/ar/**',
                            'modules/registration/locale/ar/**',
                            'modules/users/locale/ar/**',
                            'system/locale/ar/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_id: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/id/**',
                            'modules/album/locale/id/**',
                            'modules/downloads/locale/id/**',
                            'modules/forum/locale/id/**',
                            'modules/guestbook/locale/id/**',
                            'modules/help/locale/id/**',
                            'modules/library/locale/id/**',
                            'modules/mail/locale/id/**',
                            'modules/news/locale/id/**',
                            'modules/profile/locale/id/**',
                            'modules/registration/locale/id/**',
                            'modules/users/locale/id/**',
                            'system/locale/id/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_lt: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/lt/**',
                            'modules/album/locale/lt/**',
                            'modules/downloads/locale/lt/**',
                            'modules/forum/locale/lt/**',
                            'modules/guestbook/locale/lt/**',
                            'modules/help/locale/lt/**',
                            'modules/library/locale/lt/**',
                            'modules/mail/locale/lt/**',
                            'modules/news/locale/lt/**',
                            'modules/profile/locale/lt/**',
                            'modules/registration/locale/lt/**',
                            'modules/users/locale/lt/**',
                            'system/locale/lt/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_pl: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/pl/**',
                            'modules/album/locale/pl/**',
                            'modules/downloads/locale/pl/**',
                            'modules/forum/locale/pl/**',
                            'modules/guestbook/locale/pl/**',
                            'modules/help/locale/pl/**',
                            'modules/library/locale/pl/**',
                            'modules/mail/locale/pl/**',
                            'modules/news/locale/pl/**',
                            'modules/profile/locale/pl/**',
                            'modules/registration/locale/pl/**',
                            'modules/users/locale/pl/**',
                            'system/locale/pl/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_ro: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/ro/**',
                            'modules/album/locale/ro/**',
                            'modules/downloads/locale/ro/**',
                            'modules/forum/locale/ro/**',
                            'modules/guestbook/locale/ro/**',
                            'modules/help/locale/ro/**',
                            'modules/library/locale/ro/**',
                            'modules/mail/locale/ro/**',
                            'modules/news/locale/ro/**',
                            'modules/profile/locale/ro/**',
                            'modules/registration/locale/ro/**',
                            'modules/users/locale/ro/**',
                            'system/locale/ro/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_ru: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/ru/**',
                            'modules/album/locale/ru/**',
                            'modules/downloads/locale/ru/**',
                            'modules/forum/locale/ru/**',
                            'modules/guestbook/locale/ru/**',
                            'modules/help/locale/ru/**',
                            'modules/library/locale/ru/**',
                            'modules/mail/locale/ru/**',
                            'modules/news/locale/ru/**',
                            'modules/profile/locale/ru/**',
                            'modules/registration/locale/ru/**',
                            'modules/users/locale/ru/**',
                            'system/locale/ru/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_uk: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/uk/**',
                            'modules/album/locale/uk/**',
                            'modules/downloads/locale/uk/**',
                            'modules/forum/locale/uk/**',
                            'modules/guestbook/locale/uk/**',
                            'modules/help/locale/uk/**',
                            'modules/library/locale/uk/**',
                            'modules/mail/locale/uk/**',
                            'modules/news/locale/uk/**',
                            'modules/profile/locale/uk/**',
                            'modules/registration/locale/uk/**',
                            'modules/users/locale/uk/**',
                            'system/locale/uk/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            },
            lng_vi: {
                files: [
                    {
                        expand: true,
                        src: [
                            'modules/admin/locale/vi/**',
                            'modules/album/locale/vi/**',
                            'modules/downloads/locale/vi/**',
                            'modules/forum/locale/vi/**',
                            'modules/guestbook/locale/vi/**',
                            'modules/help/locale/vi/**',
                            'modules/library/locale/vi/**',
                            'modules/mail/locale/vi/**',
                            'modules/news/locale/vi/**',
                            'modules/profile/locale/vi/**',
                            'modules/registration/locale/vi/**',
                            'modules/users/locale/vi/**',
                            'system/locale/vi/**'
                        ],
                        dest: 'distributive/'
                    }
                ]
            }
        },

        // Очищаем папки и удаляем файлы
        clean: {
            dist: ['dist'],
            distributive: ['distributive']
        },

        // Сжимаем файлы
        compress: {
            dist: {
                options: {
                    archive: 'dist/mobicms_classic-<%= pkg.version %>.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_ar: {
                options: {
                    archive: 'dist/locales/ar.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_id: {
                options: {
                    archive: 'dist/locales/id.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_lt: {
                options: {
                    archive: 'dist/locales/lt.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_pl: {
                options: {
                    archive: 'dist/locales/pl.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_ro: {
                options: {
                    archive: 'dist/locales/ro.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_ru: {
                options: {
                    archive: 'dist/locales/ru.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_uk: {
                options: {
                    archive: 'dist/locales/uk.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            },
            lng_vi: {
                options: {
                    archive: 'dist/locales/vi.zip'
                },

                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: 'distributive/',
                        src: ['**']
                    }
                ]
            }
        },

        exec: {
            // Компилируем .mo файлы
            makemo_ar: {
                command: 'msgfmt -o modules/admin/locale/ar/default.mo modules/admin/locale/ar/default.po' +
                '& msgfmt -o modules/album/locale/ar/default.mo modules/album/locale/ar/default.po' +
                '& msgfmt -o modules/downloads/locale/ar/default.mo modules/downloads/locale/ar/default.po' +
                '& msgfmt -o modules/forum/locale/ar/default.mo modules/forum/locale/ar/default.po' +
                '& msgfmt -o modules/guestbook/locale/ar/default.mo modules/guestbook/locale/ar/default.po' +
                '& msgfmt -o modules/help/locale/ar/default.mo modules/help/locale/ar/default.po' +
                '& msgfmt -o modules/library/locale/ar/default.mo modules/library/locale/ar/default.po' +
                '& msgfmt -o modules/mail/locale/ar/default.mo modules/mail/locale/ar/default.po' +
                '& msgfmt -o modules/news/locale/ar/default.mo modules/news/locale/ar/default.po' +
                '& msgfmt -o modules/profile/locale/ar/default.mo modules/profile/locale/ar/default.po' +
                '& msgfmt -o modules/registration/locale/ar/default.mo modules/registration/locale/ar/default.po' +
                '& msgfmt -o system/locale/ar/system.mo system/locale/ar/system.po' +
                '& msgfmt -o modules/users/locale/ar/default.mo modules/users/locale/ar/default.po',
                stdout: false,
                stderr: true
            },
            makemo_id: {
                command: 'msgfmt -o modules/admin/locale/id/default.mo modules/admin/locale/id/default.po' +
                '& msgfmt -o modules/album/locale/id/default.mo modules/album/locale/id/default.po' +
                '& msgfmt -o modules/downloads/locale/id/default.mo modules/downloads/locale/id/default.po' +
                '& msgfmt -o modules/forum/locale/id/default.mo modules/forum/locale/id/default.po' +
                '& msgfmt -o modules/guestbook/locale/id/default.mo modules/guestbook/locale/id/default.po' +
                '& msgfmt -o modules/help/locale/id/default.mo modules/help/locale/id/default.po' +
                '& msgfmt -o modules/library/locale/id/default.mo modules/library/locale/id/default.po' +
                '& msgfmt -o modules/mail/locale/id/default.mo modules/mail/locale/id/default.po' +
                '& msgfmt -o modules/news/locale/id/default.mo modules/news/locale/id/default.po' +
                '& msgfmt -o modules/profile/locale/id/default.mo modules/profile/locale/id/default.po' +
                '& msgfmt -o modules/registration/locale/id/default.mo modules/registration/locale/id/default.po' +
                '& msgfmt -o system/locale/id/system.mo system/locale/id/system.po' +
                '& msgfmt -o modules/users/locale/id/default.mo modules/users/locale/id/default.po',
                stdout: false,
                stderr: true
            },
            makemo_lt: {
                command: 'msgfmt -o modules/admin/locale/lt/default.mo modules/admin/locale/lt/default.po' +
                '& msgfmt -o modules/album/locale/lt/default.mo modules/album/locale/lt/default.po' +
                '& msgfmt -o modules/downloads/locale/lt/default.mo modules/downloads/locale/lt/default.po' +
                '& msgfmt -o modules/forum/locale/lt/default.mo modules/forum/locale/lt/default.po' +
                '& msgfmt -o modules/guestbook/locale/lt/default.mo modules/guestbook/locale/lt/default.po' +
                '& msgfmt -o modules/help/locale/lt/default.mo modules/help/locale/lt/default.po' +
                '& msgfmt -o modules/library/locale/lt/default.mo modules/library/locale/lt/default.po' +
                '& msgfmt -o modules/mail/locale/lt/default.mo modules/mail/locale/lt/default.po' +
                '& msgfmt -o modules/news/locale/lt/default.mo modules/news/locale/lt/default.po' +
                '& msgfmt -o modules/profile/locale/lt/default.mo modules/profile/locale/lt/default.po' +
                '& msgfmt -o modules/registration/locale/lt/default.mo modules/registration/locale/lt/default.po' +
                '& msgfmt -o system/locale/lt/system.mo system/locale/lt/system.po' +
                '& msgfmt -o modules/users/locale/lt/default.mo modules/users/locale/lt/default.po',
                stdout: false,
                stderr: true
            },
            makemo_pl: {
                command: 'msgfmt -o modules/admin/locale/pl/default.mo modules/admin/locale/pl/default.po' +
                '& msgfmt -o modules/album/locale/pl/default.mo modules/album/locale/pl/default.po' +
                '& msgfmt -o modules/downloads/locale/pl/default.mo modules/downloads/locale/pl/default.po' +
                '& msgfmt -o modules/forum/locale/pl/default.mo modules/forum/locale/pl/default.po' +
                '& msgfmt -o modules/guestbook/locale/pl/default.mo modules/guestbook/locale/pl/default.po' +
                '& msgfmt -o modules/help/locale/pl/default.mo modules/help/locale/pl/default.po' +
                '& msgfmt -o modules/library/locale/pl/default.mo modules/library/locale/pl/default.po' +
                '& msgfmt -o modules/mail/locale/pl/default.mo modules/mail/locale/pl/default.po' +
                '& msgfmt -o modules/news/locale/pl/default.mo modules/news/locale/pl/default.po' +
                '& msgfmt -o modules/profile/locale/pl/default.mo modules/profile/locale/pl/default.po' +
                '& msgfmt -o modules/registration/locale/pl/default.mo modules/registration/locale/pl/default.po' +
                '& msgfmt -o system/locale/pl/system.mo system/locale/pl/system.po' +
                '& msgfmt -o modules/users/locale/pl/default.mo modules/users/locale/pl/default.po',
                stdout: false,
                stderr: true
            },
            makemo_ro: {
                command: 'msgfmt -o modules/admin/locale/ro/default.mo modules/admin/locale/ro/default.po' +
                '& msgfmt -o modules/album/locale/ro/default.mo modules/album/locale/ro/default.po' +
                '& msgfmt -o modules/downloads/locale/ro/default.mo modules/downloads/locale/ro/default.po' +
                '& msgfmt -o modules/forum/locale/ro/default.mo modules/forum/locale/ro/default.po' +
                '& msgfmt -o modules/guestbook/locale/ro/default.mo modules/guestbook/locale/ro/default.po' +
                '& msgfmt -o modules/help/locale/ro/default.mo modules/help/locale/ro/default.po' +
                '& msgfmt -o modules/library/locale/ro/default.mo modules/library/locale/ro/default.po' +
                '& msgfmt -o modules/mail/locale/ro/default.mo modules/mail/locale/ro/default.po' +
                '& msgfmt -o modules/news/locale/ro/default.mo modules/news/locale/ro/default.po' +
                '& msgfmt -o modules/profile/locale/ro/default.mo modules/profile/locale/ro/default.po' +
                '& msgfmt -o modules/registration/locale/ro/default.mo modules/registration/locale/ro/default.po' +
                '& msgfmt -o system/locale/ro/system.mo system/locale/ro/system.po' +
                '& msgfmt -o modules/users/locale/ro/default.mo modules/users/locale/ro/default.po',
                stdout: false,
                stderr: true
            },
            makemo_ru: {
                command: 'msgfmt -o modules/admin/locale/ru/default.mo modules/admin/locale/ru/default.po' +
                '& msgfmt -o modules/album/locale/ru/default.mo modules/album/locale/ru/default.po' +
                '& msgfmt -o modules/downloads/locale/ru/default.mo modules/downloads/locale/ru/default.po' +
                '& msgfmt -o modules/forum/locale/ru/default.mo modules/forum/locale/ru/default.po' +
                '& msgfmt -o modules/guestbook/locale/ru/default.mo modules/guestbook/locale/ru/default.po' +
                '& msgfmt -o modules/help/locale/ru/default.mo modules/help/locale/ru/default.po' +
                '& msgfmt -o modules/library/locale/ru/default.mo modules/library/locale/ru/default.po' +
                '& msgfmt -o modules/mail/locale/ru/default.mo modules/mail/locale/ru/default.po' +
                '& msgfmt -o modules/news/locale/ru/default.mo modules/news/locale/ru/default.po' +
                '& msgfmt -o modules/profile/locale/ru/default.mo modules/profile/locale/ru/default.po' +
                '& msgfmt -o modules/registration/locale/ru/default.mo modules/registration/locale/ru/default.po' +
                '& msgfmt -o system/locale/ru/system.mo system/locale/ru/system.po' +
                '& msgfmt -o modules/users/locale/ru/default.mo modules/users/locale/ru/default.po',
                stdout: false,
                stderr: true
            },
            makemo_uk: {
                command: 'msgfmt -o modules/admin/locale/uk/default.mo modules/admin/locale/uk/default.po' +
                '& msgfmt -o modules/album/locale/uk/default.mo modules/album/locale/uk/default.po' +
                '& msgfmt -o modules/downloads/locale/uk/default.mo modules/downloads/locale/uk/default.po' +
                '& msgfmt -o modules/forum/locale/uk/default.mo modules/forum/locale/uk/default.po' +
                '& msgfmt -o modules/guestbook/locale/uk/default.mo modules/guestbook/locale/uk/default.po' +
                '& msgfmt -o modules/help/locale/uk/default.mo modules/help/locale/uk/default.po' +
                '& msgfmt -o modules/library/locale/uk/default.mo modules/library/locale/uk/default.po' +
                '& msgfmt -o modules/mail/locale/uk/default.mo modules/mail/locale/uk/default.po' +
                '& msgfmt -o modules/news/locale/uk/default.mo modules/news/locale/uk/default.po' +
                '& msgfmt -o modules/profile/locale/uk/default.mo modules/profile/locale/uk/default.po' +
                '& msgfmt -o modules/registration/locale/uk/default.mo modules/registration/locale/uk/default.po' +
                '& msgfmt -o system/locale/uk/system.mo system/locale/uk/system.po' +
                '& msgfmt -o modules/users/locale/uk/default.mo modules/users/locale/uk/default.po',
                stdout: false,
                stderr: true
            },
            makemo_vi: {
                command: 'msgfmt -o modules/admin/locale/vi/default.mo modules/admin/locale/vi/default.po' +
                '& msgfmt -o modules/album/locale/vi/default.mo modules/album/locale/vi/default.po' +
                '& msgfmt -o modules/downloads/locale/vi/default.mo modules/downloads/locale/vi/default.po' +
                '& msgfmt -o modules/forum/locale/vi/default.mo modules/forum/locale/vi/default.po' +
                '& msgfmt -o modules/guestbook/locale/vi/default.mo modules/guestbook/locale/vi/default.po' +
                '& msgfmt -o modules/help/locale/vi/default.mo modules/help/locale/vi/default.po' +
                '& msgfmt -o modules/library/locale/vi/default.mo modules/library/locale/vi/default.po' +
                '& msgfmt -o modules/mail/locale/vi/default.mo modules/mail/locale/vi/default.po' +
                '& msgfmt -o modules/news/locale/vi/default.mo modules/news/locale/vi/default.po' +
                '& msgfmt -o modules/profile/locale/vi/default.mo modules/profile/locale/vi/default.po' +
                '& msgfmt -o modules/registration/locale/vi/default.mo modules/registration/locale/vi/default.po' +
                '& msgfmt -o system/locale/vi/system.mo system/locale/vi/system.po' +
                '& msgfmt -o modules/users/locale/vi/default.mo modules/users/locale/vi/default.po',
                stdout: false,
                stderr: true
            }
        },

        // Обновляем зависимости
        devUpdate: {
            main: {
                options: {
                    updateType: 'force',
                    semver: false
                }
            }
        }
    });

    // Загружаем нужные модули
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-exec');
    grunt.loadNpmTasks('grunt-dev-update');

    // Общая задача
    grunt.registerTask('default', []);

    grunt.registerTask('distributive', [
        'clean:dist',
        'copy:distributive',
        'compress:dist',
        'clean:distributive'
    ]);

    grunt.registerTask('makemo', [
        'exec:makemo_ar',
        'exec:makemo_id',
        'exec:makemo_lt',
        'exec:makemo_pl',
        'exec:makemo_ro',
        'exec:makemo_ru',
        'exec:makemo_uk',
        'exec:makemo_vi'
    ]);

    grunt.registerTask('locales', [
        'clean:dist',
        'clean:distributive',

        'copy:lng_ar',
        'compress:lng_ar',
        'clean:distributive',

        'copy:lng_id',
        'compress:lng_id',
        'clean:distributive',

        'copy:lng_lt',
        'compress:lng_lt',
        'clean:distributive',

        'copy:lng_pl',
        'compress:lng_pl',
        'clean:distributive',

        'copy:lng_ro',
        'compress:lng_ro',
        'clean:distributive',

        'copy:lng_ru',
        'compress:lng_ru',
        'clean:distributive',

        'copy:lng_uk',
        'compress:lng_uk',
        'clean:distributive',

        'copy:lng_vi',
        'compress:lng_vi',
        'clean:distributive'
    ]);

    // Обновление Dev Dependencies
    grunt.registerTask('upd', [
        'devUpdate:main'
    ]);
};
