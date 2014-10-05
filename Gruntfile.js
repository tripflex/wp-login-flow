'use strict';
module.exports = function ( grunt ) {

	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig(
		{
			pkg: grunt.file.readJSON( 'package.json' ),

			core: {css: 'assets/css/core', js: 'assets/js/core'},
			vendor: {js: 'assets/js/vendor', css: 'assets/css/vendor'},
			build: {js: 'assets/js/build', css: 'assets/css/build', dir: 'dist/<%= pkg.version %>/<%= pkg.name %>'},
			min: {css: 'assets/css', js: 'assets/js'},

			watch: {

				options: {},
				js: {
					files: [ '<%= core.js %>/*.js', '<%= vendor.js %>/*.js', '<%= core.js %>/**/*.js', '<%= core.js %>/**/**/*.js' ],
					tasks: [ 'concat', 'cssmin', 'uglify' ]
				},
				css: {
					files: [ '<%= core.css %>/*.css', '<%= vendor.css %>/*.css' ],
					tasks: [ 'concat', 'cssmin', 'uglify' ]
				},
				less: {
					files: [ '<%= core.css %>/*.less' ],
					tasks: [ 'less' ]
				}

			},

			less: {

				core: {
					options: {
						paths: [ "<%= core.css %>" ],
						cleancss: true
					},
					files: {
						"<%= core.css %>/style.css": "<%= core.css %>/style.less"
					}
				}
			},

			concat: {
				corecss: {
					options: {
						stripBanners: true,
						banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
						        '<%= grunt.template.today("yyyy-mm-dd") %> */'
					},
					src: [
						'<%= core.css %>/*.css'
					],
					dest: '<%= build.css %>/<%= pkg.acronym %>.css'
				},
				corejs: {
					src: [
						'<%= core.js %>/*.js',
						'<%= core.js %>/**/*.js',
						'<%= core.js %>/**/**/*.js'

					],
					dest: '<%= build.js %>/<%= pkg.acronym %>.js'
				},
				vendorcss: {
					options: {
						stripBanners: true,
						banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
						        '<%= grunt.template.today("yyyy-mm-dd") %> */'
					},
					src: [
						'<%= vendor.css %>/*.css'
					],
					dest: '<%= build.css %>/vendor.css'
				},
				vendorjs: {
					src: [
						'<%= vendor.js %>/*.js'
					],
					dest: '<%= build.js %>/vendor.js'
				}
			},

			cssmin: {
				core: {
					src: '<%= concat.corecss.dest %>',
					dest: '<%= min.css %>/<%= pkg.acronym %>.min.css'
				},
				vendor: {
					src: '<%= concat.vendorcss.dest %>',
					dest: '<%= min.css %>/vendor.min.css'
				}
			},

			uglify: {
				vendor: {
					options: {
						preserveComments: 'none',
						compress: {
							drop_console: true,
							global_defs: {
								"DEBUG": false
							}
						}
					},
					files: {
						'<%= min.js %>/vendor.min.js': [ '<%= concat.vendorjs.dest %>' ]
					}
				},
				core: {
					options: {
						preserveComments: 'none',
						compress: {
							drop_console: true,
							global_defs: {
								"DEBUG": false
							}
						}
					},
					files: {
						'<%= min.js %>/<%= pkg.acronym %>.min.js': [ '<%= concat.corejs.dest %>' ]
					}
				}
			},

			addtextdomain: {
				options: {
					textdomain: '<%= pkg.name %>'    // Project text domain.
				},
				target: {
					files: {
						src: [ '<%= build.dir %>/*.php', '<%= build.dir %>/**/*.php' ]
					}
				}
			},

			checktextdomain: {
				options: {
					text_domain: '<%= pkg.name %>',
					correct_domain: true,
					keywords: [
						'__:1,2d',
						'_e:1,2d',
						'_x:1,2c,3d',
						'esc_html__:1,2d',
						'esc_html_e:1,2d',
						'esc_html_x:1,2c,3d',
						'esc_attr__:1,2d',
						'esc_attr_e:1,2d',
						'esc_attr_x:1,2c,3d',
						'_ex:1,2c,3d',
						'_n:1,2,4d',
						'_nx:1,2,4c,5d',
						'_n_noop:1,2,3d',
						'_nx_noop:1,2,3c,4d'
					]
				},
				files: {
					src: [ '<%= build.dir %>/**/*.php' ],
					expand: true
				}
			},

			makepot: {
				target: {
					options: {
						cwd: '<%= build.dir %>/',
						domainPath: '/languages',       // Where to save the POT file.
						exclude: [],          // List of files or directories to ignore.
						mainFile: '<%= pkg.name %>.php',         // Main project file.
						potComments: '',      // The copyright at the beginning of the POT file.
						potFilename: '<%= pkg.name %>.pot',      // Name of the POT file.
						processPot: function ( pot, options ) {
							pot.headers[ 'report-msgid-bugs-to' ] = 'http://plugins.smyl.es';
							pot.headers[ 'language-team' ] = 'sMyles <get@smyl.es>';
							pot.headers[ 'last-translator' ] = 'Myles McNamara';
							return pot;
						},
						type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
						updateTimestamp: true // Whether the POT-Creation-Date should be updated without other changes.
					}
				}
			},

			po2mo: {
				files: {
					src: '<%= build.dir %>/languages/*.po',
					expand: true
				}
			},

			exec: {
				npmUpdate: {
					command: 'npm update'
				},
				txpull: {
					cmd: 'tx pull -a -f'
				},
				txpush: {
					cmd: 'tx push -s'
				}
			},

			copy: {
				deploy: {
					src: [
						'**', '!Gruntfile.js',
						'!dist/**',
						'!package.json',
						'!node_modules/**',
						'!includes/**/node_modules/**',
						'!includes/**/Gruntfile.js',
						'!includes/**/package.json',
						'!assets/**/build/**',
						'!assets/**/core/**',
						'!assets/**/vendor/**',
						'!includes/**/assets/**/build/**',
						'!includes/**/assets/**/core/**',
						'!includes/**/assets/**/vendor/**',
					],
					dest: '<%= build.dir %>/',
					expand: true
				},
				pot: {
					src: '<%= build.dir %>/languages/<%= pkg.name %>.pot',
					dest: 'languages/<%= pkg.name %>.pot',
					expand: false
				}
			},

			clean: {
				deploy: {
					src: [ '<%= build.dir %>/' ]
				}
			},

			compress: {
				main: {
					options: {
						archive: 'dist/<%= pkg.name %>_<%= pkg.version %>.zip'
					},
					expand: true,
					cwd: '<%= build.dir %>/',
					dest: '<%= pkg.name %>',
					src: [ '**/**' ]
				}
			},

			replace: {
				deploy: {
					options: {
						patterns: [
							{
								match: 'timestamp',
								replacement: '<%= grunt.template.today() %>'
							},
							{
								match: 'version',
								replacement: '<%= pkg.version %>'
							}
						]
					}, files: [
						{
							expand: true,
							flatten: false,
							src: [ '*.php', '**/*.php', '!node_modules/**/*.php', '!dist/**/*.php' ],
							dest: '<%= build.dir %>/'
						}
					]
				},

				since: {
					options: {
						patterns: [
							{
								match: 'version',
								replacement: '<%= pkg.version %>'
							}
						]
					}, files: [
						{
							expand: true,
							flatten: false,
							src: [ '*.php', '**/*.php', '!node_modules/**/*.php', '!dist/**/*.php' ]
						}
					]
				}
			},

			autoprefixer: {
				options: {
					browsers: [
						'Android 2.3',
						'Android >= 4',
						'Chrome >= 20',
						'Firefox >= 24', // Firefox 24 is the latest ESR
						'Explorer >= 8',
						'iOS >= 6',
						'Opera >= 12',
						'Safari >= 6'
					]
				},
				built: {
					options: {
						map: false
					},
					src: '<%= build.css %>/*.css'
				}
			}

		}
	);

	grunt.registerTask( 'all', [ 'less', 'concat', 'autoprefixer', 'cssmin', 'uglify' ] );
	grunt.registerTask( 'css', [ 'less:core', 'concat:corecss', 'autoprefixer', 'cssmin:core', 'concat:vendorcss', 'cssmin:vendor' ] );
	grunt.registerTask( 'vendor', [ 'concat:vendorcss', 'concat:vendorjs', 'autoprefixer', 'cssmin:vendor', 'uglify:vendor' ] );
	grunt.registerTask( 'core', [ 'less:core', 'concat:corecss', 'autoprefixer', 'concat:corejs', 'cssmin:core', 'uglify:core' ] );

	grunt.registerTask(
		'deploy', [
			'less',
			'concat',
			'autoprefixer',
			'cssmin',
			'uglify',
			'clean:deploy',
			'exec:txpull',
			'copy:deploy',
			'replace:deploy',
			'addtextdomain',
			'checktextdomain',
			'makepot',
			'copy:pot',
			'po2mo',
			'compress',
			'exec:txpush'
		]
	);

};