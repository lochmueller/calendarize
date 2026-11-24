/**
 * Let the back link of the detail view return to the exact list/calendar view the
 * visitor came from (e.g. a specific month), not only to the configured list page.
 *
 * The link keeps pointing to the configured list page (fallback without JavaScript
 * or when the visitor did not come from the list page). If the referrer is the list
 * page (same origin, same path or a sub path of it, and all query parameters of the
 * link present), the href is replaced by the referrer.
 */
document.addEventListener('DOMContentLoaded', function () {
  let backLinks = document.querySelectorAll('[data-calendarize-back-link]');
  if (!backLinks.length || !document.referrer) {
    return;
  }

  let referrer;
  try {
    referrer = new URL(document.referrer);
  } catch (e) {
    return;
  }

  if (referrer.origin !== window.location.origin || referrer.href === window.location.href) {
    return;
  }

  let normalizePath = function (path) {
    return path.length > 1 ? path.replace(/\/+$/, '') : path;
  };

  backLinks.forEach(function (backLink) {
    let target;
    try {
      target = new URL(backLink.href, window.location.href);
    } catch (e) {
      return;
    }

    if (target.origin !== referrer.origin) {
      return;
    }

    let targetPath = normalizePath(target.pathname);
    let referrerPath = normalizePath(referrer.pathname);
    let pathPrefix = targetPath === '/' ? '/' : targetPath + '/';
    if (referrerPath !== targetPath && referrerPath.indexOf(pathPrefix) !== 0) {
      return;
    }

    for (let [key, value] of target.searchParams) {
      if (referrer.searchParams.get(key) !== value) {
        return;
      }
    }

    backLink.href = referrer.href;
  });
});
