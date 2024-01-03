const {exec} = require('child_process');
const replace = require('replace-in-file');
const date = new Date();
const os = require("os");
const userInfo = os.userInfo();
const Hashids = require('hashids/cjs');

// config files to replace version info in
const phpFiles = ['./newspack-dbn/footer.php', './newspack-rwp/footer.php'];
const cssFiles = ['./newspack-dbn/sass/style.scss', './newspack-rwp/sass/style.scss'];

// get current branch name
exec('git rev-parse --abbrev-ref HEAD', (err, stdout, stderr) => {
	if (err) {
		console.error(err.message);
	}

	if (typeof stdout === 'string') {

		const branchName = stdout.trim();
		const buildInfo = `<!--Version: ${branchName} - ${date.toISOString()} - ${userInfo.username}-->`;
		const hashids = new Hashids('newspack-theme');
		const buildHash = hashids.encode(Date.now());

		console.log(`Setting branch name, build time and build username to footer.php ${buildInfo} ...`);

		const options = {
			files: phpFiles,
			from: /(\n)+?(<!--Version:.*-->)?(\n)?<\/body>/gm,
			to: `\n${buildInfo}\n</body>`,
		};

		replace(options, (error, results) => {
			if (error) {
				return console.error('Error occurred:', error);
			}
			console.log('Replacement results:', results);
		});

		// add branch name also to sass.scss, if we are master aka newspack-dbn, delete branch name after version no.
		if (branchName !== 'newspack-dbn') {
			const options = {
				files: cssFiles,
				from: /(Version: \d+\.\d+\.\d+)(_|-.*)?/g,
				to: `$1-${buildHash}_${branchName}`,
			};

			replace(options, (error, results) => {
				if (error) {
					return console.error('Error occurred:', error);
				}
				console.log('Replacement results:', results);
			});
		} else {
			const options = {
				files: cssFiles,
				from: /(Version: \d+\.\d+\.\d+)(_|-.*)?/g,
				to: `$1-${buildHash}`,
			};

			replace(options, (error, results) => {
				if (error) {
					return console.error('Error occurred:', error);
				}
				console.log('Replacement results:', results);
			});
		}

	}

});
