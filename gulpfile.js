'use strict';

//Production deps
const bower = require('gulp-bower');
const del = require('del');
const gulp = require('gulp');
const gulpif = require('gulp-if');
const htmlmin = require('gulp-htmlmin');
const mergeStream = require('merge-stream');
const polymer = require('polymer-build');
const replace = require('gulp-replace');
const runSequence = require('run-sequence');
const uglify = require('gulp-uglify');

//Utility deps
const yargs = require('yargs')
const gutil = require('gulp-util');

//Options
const root = 'src/Rotalia/FrontendBundle/Resources/src';
const buildRoot = 'src/Rotalia/FrontendBundle/Resources/public';
let options = require('./'+root+'/polymer.json');
options.root = __dirname + '/' + root;
const polymerProject = new polymer.PolymerProject(options);


const minifyOptions = {
	html: {
		removeComments: true,
		collapseWhitespace: true,
		minifyCSS: true //polymerProject.splitHtml, ei eralda css-i
	}
};

// Argument check
const argv = yargs.boolean('bundled').argv;
if (Object.keys(argv).length > 3) {
	gutil.log('----------------------------');
	gutil.log('Unknown optional arguments!!');
	gutil.log('----------------------------');
}


//Dev deps
try {
	var jsValidate = require('gulp-jsvalidate');
}
catch(err) {
    if(argv['_'][0] === 'build-dev') {
		gutil.log('run "npm install", without --production flag, to run "build-dev"');
		process.exit(1);
	}
}

//Utility functions
function string_src(filename, string) {
  var src = require('stream').Readable({ objectMode: true })
  src._read = function () {
    this.push(new gutil.File({
      cwd: "",
      base: "",
      path: filename,
      contents: new Buffer(string)
    }))
    this.push(null)
  }
  return src
}


/////////////////////////////
//          Tasks          //
/////////////////////////////

gulp.task('bower-install', function() {
	//Install bower dependencies
	return bower({cwd: root})
});

gulp.task('validate', function() {
	//Check js for errors
	return polymerProject.sources()
		.pipe(polymerProject.splitHtml())
		.once('data', function() { gutil.log('Running jslint...'); })
		.pipe(gulpif(/\.js$/, jsValidate()))
		.pipe(polymerProject.rejoinHtml())
});

gulp.task('build-temp', function() {	
	//Build files into temp dir
	gutil.log('Reading source files...');
	let sourcesStream = polymerProject.sources()
		.pipe(polymerProject.splitHtml())
		.pipe(gulpif(/\.js$/, uglify()))
		.pipe(gulpif(/\.html$/, htmlmin(minifyOptions.html)))
		.pipe(polymerProject.rejoinHtml())
		//Millegipärast resolveurl läheb bundled versiooniga katki
		.pipe(gulpif(argv.bundled, replace('resolveUrl("kassa', 'resolveUrl("src/kassa')))  

	gutil.log('Reading dependencies...');
	let depsStream = polymerProject.dependencies()
		.pipe(polymerProject.splitHtml())
		.pipe(gulpif(/\.js$/, uglify()))
		.pipe(gulpif(/\.html$/, htmlmin(minifyOptions.html)))
		.pipe(polymerProject.rejoinHtml())
	
	return mergeStream(sourcesStream, depsStream)
      .once('data', function() { gutil.log('Analyzing build dependencies...'); })
      .pipe(polymerProject.analyzer)
      .once('data', function() { gutil.log('Moving to temp directory...'); })
      .pipe(gulpif(argv.bundled, polymerProject.bundler))
      .pipe(gulp.dest(buildRoot+'/_temp'))
});

gulp.task('move-files', function() {
	//Move files, that weren't moved by polymer-build, to temp folder
	gutil.log('Moving static files...');
	return gulp.src([root+'/manifest.json', root+'/js/main.js', root+'/bower_components/webcomponentsjs/webcomponents-lite.min.js', root+'/../views/Default/index.html.twig', root+'/images/**/*.png'], {base: root})
		.pipe(gulpif(/\.js$/, uglify()))
        .pipe(gulp.dest(buildRoot + '/_temp/' + root))
});

gulp.task('gen-sw', function(cb) {
	//Generate service worker in the temp folder
	gutil.log('Generating service worker...');
	polymer.generateServiceWorker({
		buildRoot: buildRoot + '/_temp/' + root,
		project: polymerProject,
		bundled: argv.bundled,
		swConfig: {
			staticFileGlobs: ['manifest.json', 'js/main.js', 'bower_components/webcomponentsjs/webcomponents-lite.min.js', 'images/icons/*.png', '../views/Default/index.html.twig']
		}
	}).then(function(fileContents) {
		string_src('service-worker.js', fileContents.toString().split('["/').join('["/bundles/rotaliafrontend/'))
			.pipe(replace(/"[\w\d\/]+index\.html\.twig"/gi, '"/fe/"'))
			.pipe(uglify())
			.pipe(gulp.dest(buildRoot + '/_temp/' + root))
			.on('finish', cb)
	});	
});


gulp.task('rm-temp', function() {	  
	// Remove temp
	return gulp.src(buildRoot + '/_temp/' + root + '/**/*', {base: buildRoot + '/_temp/' + root})
			.once('data', function() { gutil.log('Moving all files to build directory...'); })
		   .pipe(gulp.dest(buildRoot))
		   .on('finish', function(){
					gutil.log('Removing temp directory...');
					del(buildRoot + '/_temp');
			   })

});

gulp.task('build', function(cb) {
	//Build for production
	runSequence('bower-install', ['build-temp', 'move-files'], 'gen-sw', 'rm-temp', cb)

});

gulp.task('build-dev', function(cb) {
	//Build for development (dont update or install
	runSequence('validate', ['build-temp', 'move-files'], 'rm-temp', cb)

});

