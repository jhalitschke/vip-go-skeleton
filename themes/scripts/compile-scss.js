const fs = require("fs");
const chokidar = require("chokidar");
const postcss = require("postcss");
const sass = require("sass");
const rtlcss = require("rtlcss");
const postcssFocusWithin = require("postcss-focus-within");

if (!fs.existsSync("./newspack-theme/styles")) {
	fs.mkdirSync("./newspack-theme/styles");
}

if (!fs.existsSync("./newspack-sacha/styles")) {
	fs.mkdirSync("./newspack-sacha/styles");
}

if (!fs.existsSync("./newspack-scott/styles")) {
	fs.mkdirSync("./newspack-scott/styles");
}

if (!fs.existsSync("./newspack-nelson/styles")) {
	fs.mkdirSync("./newspack-nelson/styles");
}

if (!fs.existsSync("./newspack-katharine/styles")) {
	fs.mkdirSync("./newspack-katharine/styles");
}

if (!fs.existsSync("./newspack-joseph/styles")) {
	fs.mkdirSync("./newspack-joseph/styles");
}

if (!fs.existsSync("./newspack-dbn/styles")) {
	fs.mkdirSync("./newspack-dbn/styles");
}

if (!fs.existsSync("./newspack-rwp/styles")) {
	fs.mkdirSync("./newspack-rwp/styles");
}

/**
 * Save a file do disk.
 */
const saveFile = (fileName, content) => {
	fs.writeFile(fileName, content, function (err) {
		if (err) {
			console.log("ERROR while saving file", fileName, "->", err);
		}
	});
};

/**
 * Compile a Sass file to CSS.
 * @param  {string} inFile  Sass file path
 * @param  {string} outFile out file path
 * @param  {bool} withRTL Whether to save RTL version additionally
 */
const compileSassFile = ({ inFile, outFile, withRTL }) =>
	new Promise((resolve, reject) => {
		sass.render(
			{
				file: inFile,
				outputStyle: "expanded",
				outFile,
			},
			function (error, result) {
				if (error) {
					console.log("ERROR in sass compilation", error);
					reject(error);
				} else {
					// process the file with PostCSS
					postcss([postcssFocusWithin])
						.process(result.css, { from: inFile, to: outFile })
						.then((result) => {
							// save the file
							saveFile(outFile, result.css);
							// save the RTL version file
							if (withRTL) {
								saveFile(
									outFile.replace(".css", "-rtl.css"),
									rtlcss.process(result.css)
								);
							}

							resolve(outFile);
						});
				}
			}
		);
	});

const compileAllStylesheets = () => {
	Promise.all(SASS_STYLESHEETS.map(compileSassFile)).then((files) => {
		console.log(`processed ${files.length} SCSS files ✨
`);
	});
};

const SASS_STYLESHEETS = [
	{
		inFile: "newspack-theme/sass/style.scss",
		outFile: "newspack-theme/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-theme/sass/style-editor.scss",
		outFile: "newspack-theme/styles/style-editor.css",
	},
	{
		inFile: "newspack-theme/sass/style-editor-overrides.scss",
		outFile: "newspack-theme/styles/style-editor-overrides.css",
	},
	{
		inFile: "newspack-theme/sass/style-editor-customizer.scss",
		outFile: "newspack-theme/styles/style-editor-customizer.css",
	},
	{
		inFile: "newspack-theme/sass/plugins/woocommerce.scss",
		outFile: "newspack-theme/styles/woocommerce.css",
		withRTL: true,
	},
	{
		inFile: "newspack-theme/sass/plugins/trust-indicators.scss",
		outFile: "newspack-theme/styles/trust-indicators.css",
		withRTL: true,
	},
	{
		inFile: "newspack-theme/sass/plugins/newspack-sponsors.scss",
		outFile: "newspack-theme/styles/newspack-sponsors.css",
		withRTL: true,
	},
	{
		inFile: "newspack-theme/sass/plugins/newspack-sponsors-editor.scss",
		outFile: "newspack-theme/styles/newspack-sponsors-editor.css",
		withRTL: true,
	},
	{
		inFile: "newspack-theme/tribe-events/tribe-events.scss",
		outFile: "newspack-theme/tribe-events/tribe-events.css",
	},
	{
		inFile: "newspack-theme/sass/print.scss",
		outFile: "newspack-theme/styles/print.css",
	},
	// Newspack Sacha Child theme
	{
		inFile: "newspack-sacha/sass/style.scss",
		outFile: "newspack-sacha/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-sacha/sass/style-editor.scss",
		outFile: "newspack-sacha/styles/style-editor.css",
	},
	{
		inFile: "newspack-sacha/sass/child-style-editor-overrides.scss",
		outFile: "newspack-sacha/styles/child-style-editor-overrides.css",
	},
	{
		inFile: "newspack-sacha/tribe-events/tribe-events.scss",
		outFile: "newspack-sacha/tribe-events/tribe-events.css",
	},
	// Newspack Scott Child theme
	{
		inFile: "newspack-scott/sass/style.scss",
		outFile: "newspack-scott/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-scott/sass/style-editor.scss",
		outFile: "newspack-scott/styles/style-editor.css",
	},
	{
		inFile: "newspack-scott/tribe-events/tribe-events.scss",
		outFile: "newspack-scott/tribe-events/tribe-events.css",
	},
	// Newspack Nelson Child theme
	{
		inFile: "newspack-nelson/sass/style.scss",
		outFile: "newspack-nelson/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-nelson/sass/style-editor.scss",
		outFile: "newspack-nelson/styles/style-editor.css",
	},
	{
		inFile: "newspack-nelson/tribe-events/tribe-events.scss",
		outFile: "newspack-nelson/tribe-events/tribe-events.css",
	},
	// Newspack Katharine Child theme
	{
		inFile: "newspack-katharine/sass/style.scss",
		outFile: "newspack-katharine/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-katharine/sass/style-editor.scss",
		outFile: "newspack-katharine/styles/style-editor.css",
	},
	{
		inFile: "newspack-katharine/tribe-events/tribe-events.scss",
		outFile: "newspack-katharine/tribe-events/tribe-events.css",
	},
	// Newspack Joseph Child theme
	{
		inFile: "newspack-joseph/sass/style.scss",
		outFile: "newspack-joseph/style.css",
		withRTL: true,
	},
	{
		inFile: "newspack-joseph/sass/style-editor.scss",
		outFile: "newspack-joseph/styles/style-editor.css",
	},
	{
		inFile: "newspack-joseph/tribe-events/tribe-events.scss",
		outFile: "newspack-joseph/tribe-events/tribe-events.css",
	},
	// Newspack DBN
	{
		inFile: "newspack-dbn/sass/style.scss",
		outFile: "newspack-dbn/style.css",
		withRTL: false,
	},
	// Newspack DBN - Default
	{
		inFile: "newspack-dbn/sass/publications/default/style-base.scss",
		outFile: "newspack-dbn/default-style.css",
		withRTL: false,
	},
	// Newspack DBN - Imtest
	{
		inFile: "newspack-dbn/sass/publications/imtest/style-base.scss",
		outFile: "newspack-dbn/imtest-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/imtest/style-editor.scss",
		outFile: "newspack-dbn/styles/imtest-style-editor.css",
	},
	// Newspack DBN - Eatclub
	{
		inFile: "newspack-dbn/sass/publications/eatclub/style-base.scss",
		outFile: "newspack-dbn/eatclub-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/eatclub/style-editor.scss",
		outFile: "newspack-dbn/styles/eatclub-style-editor.css",
	},
	// Newspack DBN - Futurezone
	{
		inFile: "newspack-dbn/sass/publications/futurezone/style-base.scss",
		outFile: "newspack-dbn/futurezone-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/futurezone/style-editor.scss",
		outFile: "newspack-dbn/styles/futurezone-style-editor.css",
	},
	// Newspack DBN - GoFeminin
	{
		inFile: "newspack-dbn/sass/publications/gofeminin/style-base.scss",
		outFile: "newspack-dbn/gofeminin-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/gofeminin/style-editor.scss",
		outFile: "newspack-dbn/styles/gofeminin-style-editor.css",
	},
	// Newspack DBN - GoldeneKamera
	{
		inFile: "newspack-dbn/sass/publications/goldenekamera/style-base.scss",
		outFile: "newspack-dbn/goldenekamera-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/goldenekamera/style-editor.scss",
		outFile: "newspack-dbn/styles/goldenekamera-style-editor.css",
	},
	// Newspack DBN - WMN
	{
		inFile: "newspack-dbn/sass/publications/wmn/style-base.scss",
		outFile: "newspack-dbn/wmn-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/wmn/style-editor.scss",
		outFile: "newspack-dbn/styles/wmn-style-editor.css",
	},
	// Newspack RWP theme
	{
		inFile: "newspack-rwp/sass/style.scss",
		outFile: "newspack-rwp/style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-rwp/sass/style-editor.scss",
		outFile: "newspack-rwp/styles/style-editor.css",
	},
];

// initial run
compileAllStylesheets();

// run watcher if `--watch` argument present
if (process.argv.some((arg) => arg.startsWith("--watch"))) {
	console.log(`watching the scss files…
`);

	chokidar.watch("newspack-dbn/sass/**/*.scss").on("change", (path) => {
		console.log(`updated: ${path}
`);

		compileAllStylesheets();
	});
}
