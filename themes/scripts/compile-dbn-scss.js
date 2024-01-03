const fs = require("fs");
const chokidar = require("chokidar");
const postcss = require("postcss");
const sass = require("sass");
const rtlcss = require("rtlcss");
const postcssFocusWithin = require("postcss-focus-within");

if (!fs.existsSync("./newspack-dbn/styles")) {
	fs.mkdirSync("./newspack-dbn/styles");
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
	// Newspack DBN - Selfies
	{
		inFile: "newspack-dbn/sass/publications/selfies/style-base.scss",
		outFile: "newspack-dbn/selfies-style.css",
		withRTL: false,
	},
	{
		inFile: "newspack-dbn/sass/publications/selfies/style-editor.scss",
		outFile: "newspack-dbn/styles/selfies-style-editor.css",
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
];

// initial run
compileAllStylesheets();

// run watcher if `--watch` argument present
if (process.argv.some((arg) => arg.startsWith("--watch"))) {
	console.log(`watching the scss files…
`);

	chokidar.watch("newspack-theme/sass/**/*.scss").on("change", (path) => {
		console.log(`updated: ${path}
`);

		compileAllStylesheets();
	});
}
