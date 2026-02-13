## Site Links

* Main: https://main-bvxea6i-7izlvla7egqey.au.platformsh.site/
* Dev: https://dev-54ta5gq-7izlvla7egqey.au.platformsh.site/

## Drupal 11 Barrio Bootstrap 5 Starter
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
* DDEV local environment (ddev-upsun addon included)
* Platform.sh / Upsun hosted
* Custom sub-theme of Barrio


### Common Commands
```
ddev ssh
cd /var/www/html/web/themes/custom/custom_bootstrap_sass
```

Site urls:

```
https://dev-54ta5gq-7izlvla7egqey.au.platformsh.site/
https://main-bvxea6i-7izlvla7egqey.au.platformsh.site/
```

### Important workflow customizations:

Gulp -- hide bootstrap sass errors:  https://github.com/twbs/bootstrap/issues/40962
```
quietDeps: true,  // <--- ADD THIS LINE (Silences dependency warnings)
silenceDeprecations: ['import', 'global-builtin', 'color-functions']
logger: dartSass.Logger.silent, // hides *all* sass warnings
```

Gulp -- running from inside the container -- **EXPERIMENTAL**

By default, if you run gulp from inside the docker container, the localhost:3000 is not accessible via the browser. You could run npx gulp from outside the container(without ddev ssh) as a temporary fix, but the idea is, we wanna use docker container's exact node, npm and gulp versions. E.g., you might be using two drupal projects one using Drupal 9 with Gulp v4. Then you'd need to manually adjust nvm etc to use correct gulp for individual projects. The whole point of docker is to circumvent that issue.

So, the solution is either manually expose port 3000 of the container to our browser or use ddev-browserSYnc addon(not working):


ddev add-on get ddev/ddev-browsersync -- Not working yet. This prolly spins up it's own browserSync server.


Or, 
```
# .ddev/config.yaml

web_extra_exposed_ports:
  - name: browsersync
    container_port: 3000
    http_port: 3000
    https_port: 3001
```

Then in gulpfile:

```
function serve(done) {
  browserSync.init({
    proxy: {
      target: 'http://localhost',

      reqHeaders: {
        'Host': DDEV_HOSTNAME,
        'X-Forwarded-Host': DDEV_HOSTNAME,
        'X-Forwarded-Proto': 'https',
        'X-Forwarded-Port': HTTPS_PORT,
        // 1. DISABLE COMPRESSION (Critical for Injection)
        // If Drupal sends GZIP content, BrowserSync can't read/inject the script.
        'Accept-Encoding': 'identity' 
      },

      proxyRes: [function (proxyRes, req, res) {
        if (proxyRes.headers.location) {
          var original = proxyRes.headers.location;
          var safeHost = DDEV_HOSTNAME.replace(/\./g, '\\.');
          var regex = new RegExp('https?:\/\/(localhost|' + safeHost + ')(:\\d+)?');
          var replacement = 'https://' + DDEV_HOSTNAME + ':' + HTTPS_PORT;
          proxyRes.headers.location = original.replace(regex, replacement);
        }
      }]
    },

    // 2. SOCKET CONFIGURATION (Restored)
    // explicitly tells the browser to use the Secure URL for the socket
    socket: {
        domain: 'https://' + DDEV_HOSTNAME + ':' + HTTPS_PORT
    },

    listen: '0.0.0.0',
    port: 3000,
    ui: false,
    open: false,    
    online: false,
    logLevel: "silent"

  }, function(err, bs) {
    console.log('\n');
    console.log('\x1b[32m%s\x1b[0m', '---------------------------------------------------');
    console.log('\x1b[36m%s\x1b[0m', ' DDEV BrowserSync Ready!');
    console.log(' Access URL: \x1b[35mhttps://' + DDEV_HOSTNAME + ':' + HTTPS_PORT + '\x1b[0m');
    console.log('\x1b[32m%s\x1b[0m', '---------------------------------------------------');
    console.log('\n');
    done();
  });

  gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload);
  gulp.watch(paths.scss.componentsWatch, createCssComponent);
}
```

We basically bind yourname.ddev.site:3001 to docker web container's internal localhost url exposed from gulp. But, then we start to see problem of login forms and other forms action value being wrong. Drupal redirects to localhost based url from php. So, to counter this we redirect localhost based urls back to our <yoursite:3001>


Another thing is running gulp from outside the container works, but but when we submit a form, e.g., login it takes you to yoursite.ddev.site url not the localhost url. This makes my method better in on way that it redirects to same :3001 port.

There might be some scanarios where redirect logic might need to be improved as I am not familiar with advanced Drupal/Symphony routing.