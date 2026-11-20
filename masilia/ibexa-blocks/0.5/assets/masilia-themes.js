const path = require('path');
const { execFileSync } = require('child_process');

/**
 * Adds to the Encore build of the project the assets of the themes of masilia/ibexa-blocks: each theme.yaml says which entry it
 * builds and from which sources (php bin/console masilia:blocks:themes --json). Without it a theme is not built, and the
 * layout of a site that uses it answers 500 ('Could not find the entry "acme.css" in entrypoints.json').
 *
 * In webpack.config.js, before Encore.getWebpackConfig():
 *
 *     require('./assets/masilia-themes.js')(Encore, __dirname);
 */
module.exports = (Encore, projectDir) => {
    let themes;
    try {
        const output = execFileSync('php', ['bin/console', 'masilia:blocks:themes', '--json', '--no-debug'], {
            cwd: projectDir,
            encoding: 'utf8',
            stdio: ['ignore', 'pipe', 'ignore'],
        });
        themes = JSON.parse(output.slice(output.indexOf('{')));
    } catch (error) {
        throw new Error(`The theme entries could not be read (php bin/console masilia:blocks:themes --json): ${error.message}`);
    }
    if (themes.problems.length > 0) {
        throw new Error(`Theme assets are wrong:\n - ${themes.problems.join('\n - ')}`);
    }
    Object.entries(themes.entries).forEach(([entry, source]) => {
        Encore.addEntry(entry, [path.resolve(projectDir, source)]);
    });
};
