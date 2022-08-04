const fs = require("fs");
const DirCompressor = require('dir-compressor');
const version = require('../package.json').version;

if (!fs.existsSync('./release')) {
    fs.mkdirSync('./release');
}

// Create an array with the files and directories to exclude.
const excludes = ['release', '.vscode', '.gitignore', '.git', '*.iml', 'scripts', 'node_modules', 'composer.json','composer.lock','package.json','package-lock.json'];

const archive = new DirCompressor('../kaltura-video', 'release/kaltura-video_' + version + '.zip', excludes, false);

archive.createZip();
var Rsync = require('rsync');
 
// Build the command
var rsync = new Rsync()
  .shell('ssh')
  .flags('az')
  .source('../kaltura-video')
  .destination('../../funke-digital/plugins/kaltura-video');
 
// Execute the command
rsync.execute(function(error, code, cmd) {
    // we're done
});