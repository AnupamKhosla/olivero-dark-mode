## Site Links

* Main: https://main-bvxea6i-7izlvla7egqey.au.platformsh.site/
* Dev: https://dev-54ta5gq-7izlvla7egqey.au.platformsh.site/

# Drupal 11 Barrio Bootstrap 5 Starter
A teaching-focused Drupal site that demonstrates how the **Barrio** theme integrates and implements **Bootstrap** in a real project.

This as an open-source **"Starter Kit"** for the Drupal community.

The goal is to create a complete **Reference Implementation** of the Bootstrap 5 Barrio theme. Unlike a blank theme installation, this project will come pre-configured with working examples of every Bootstrap component (Carousels, Tooltips, Modals, Accordions) implemented correctly using Drupal Blocks and Views.

It effectively functions as a **"Living Documentation"** — developers can clone it to see exactly how to implement complex Bootstrap features in Drupal 11 without guessing.

## Project Goals

* Teach how Barrio uses Bootstrap structure, utilities, and components
* Show practical theme layer patterns in Drupal 11
* Provide a clean reference implementation for learning and demos
* Keep the setup straightforward and reproducible

## Tech Stack

* Drupal 11
* Barrio base theme
* Bootstrap
* DDEV local environment
* Custom sub-theme of Barrio


###Common Commands

ddev ssh
cd /var/www/html/web/themes/custom/custom_bootstrap_sass

### Important workflow customizations:

Gulp -- hide bootstrap errors: <addLinkOfBsOfficialSuggestionToIgnoreSassErrors>

quietDeps: true,  // <--- ADD THIS LINE (Silences dependency warnings)
silenceDeprecations: ['import', 'global-builtin', 'color-functions']
logger: dartSass.Logger.silent, // hides *all* sass warnings

Gulp -- running from inside the container -- **EXPERIMENTAL**

By default, if you run gulp from inside the docker container, the localhost:3000 is not accessible via the browser. You could run npx gulp from outside the container(without ddev ssh) as a temporary fix, but the idea is, we wanna use docker container's exact node, npm and gulp versions. E.g., you might be using two drupal projects one using Drupal 9 with Gulp v4. Then you'd need to manually adjust nvm etc to use correct gulp for individual projects. The whole point of docker is to circumvent that issue.

So, the solution is either manually expose port 3000 of the container to our browser or use ddev-browserSYnc addon(not working):


ddev add-on get ddev/ddev-browsersync -- Not working yet. This prolly spins up it's own browserSync server.


Or, 

.ddev/config.yaml

web_extra_exposed_ports:
  - name: browsersync
    container_port: 3000
    http_port: 3000
    https_port: 3001

Then in gulpfile:

function serve(done) {
  browserSync.init({
    proxy: 'http://localhost',     
    listen: '0.0.0.0',
    port: 3000,
    ui: false,
    open: false,    
    online: false,
    socket: {
        domain: 'dp.ddev.site:3001'
    }    
  }, done);

  gulp
    .watch([paths.scss.watch, paths.scss.bootstrap], styles)
    .on('change', browserSync.reload);
  gulp.watch(paths.scss.componentsWatch, createCssComponent);
}

We basically bind yourname.ddev.site:3001 to docker web container's internal localhost url exposed from gulp. But, then we start to see problem of login forms and other forms action value being wrong. Drupal redirects to localhost based url from php.

I think the sacrifice of using global Gulp is worth it. We can simply manually use nvm or other techniques like gulp4 and gulp3 binaries.

Another thing is running gulp from outside the container works, but but when we submit a form, e.g., login it takes you to yoursite.ddev.site url not the localhost url. Regardless, if someone wants to use Gulp from inside the container they can use gulpfile_docker.js
