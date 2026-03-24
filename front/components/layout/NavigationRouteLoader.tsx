'use client';

import LogoLoader from '#/components/ui/LogoLoader.component';
import { usePathname } from 'next/navigation';
import { useEffect, useRef, useState } from 'react';

function normalizePath(path: string) {
  if (!path) return '/';
  const withoutQuery = path.split('?')[0] ?? '/';
  if (withoutQuery !== '/' && withoutQuery.endsWith('/')) {
    return withoutQuery.slice(0, -1);
  }
  return withoutQuery;
}

const MIN_VISIBLE_MS = 280;

export default function NavigationRouteLoader() {
  const pathname = usePathname();
  const [visible, setVisible] = useState(false);
  const pathnameRef = useRef(pathname);
  const shownAtRef = useRef(0);
  const visibleRef = useRef(false);

  useEffect(() => {
    pathnameRef.current = pathname;
  }, [pathname]);

  useEffect(() => {
    visibleRef.current = visible;
  }, [visible]);

  useEffect(() => {
    if (!visibleRef.current) return;

    const elapsed = Date.now() - shownAtRef.current;
    const wait = Math.max(0, MIN_VISIBLE_MS - elapsed);
    const id = window.setTimeout(() => setVisible(false), wait);
    return () => window.clearTimeout(id);
  }, [pathname]);

  useEffect(() => {
    const openLoaderForHref = (hrefAttr: string | null) => {
      if (!hrefAttr) return;
      const trimmed = hrefAttr.trim();
      if (
        !trimmed ||
        trimmed.startsWith('#') ||
        trimmed.startsWith('mailto:') ||
        trimmed.startsWith('tel:') ||
        trimmed.startsWith('javascript:')
      ) {
        return;
      }

      let path: string;
      try {
        const url = new URL(trimmed, window.location.origin);
        if (url.origin !== window.location.origin) return;
        path = url.pathname;
      } catch {
        return;
      }

      if (normalizePath(path) === normalizePath(pathnameRef.current)) return;

      shownAtRef.current = Date.now();
      setVisible(true);
    };

    const onClickCapture = (event: MouseEvent) => {
      if (event.defaultPrevented || event.button !== 0) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      const target = event.target;
      if (!(target instanceof Element)) return;
      const anchor = target.closest('a[href]');
      if (!anchor || !(anchor instanceof HTMLAnchorElement)) return;
      if (anchor.getAttribute('aria-disabled') === 'true') return;
      if (anchor.dataset.noRouteLoader === 'true') return;

      const targetAttr = anchor.getAttribute('target');
      if (targetAttr && targetAttr !== '' && targetAttr !== '_self') return;

      const download = anchor.getAttribute('download');
      if (download !== null) return;

      openLoaderForHref(anchor.getAttribute('href'));
    };

    const onPopState = () => {
      shownAtRef.current = Date.now();
      setVisible(true);
    };

    document.addEventListener('click', onClickCapture, true);
    window.addEventListener('popstate', onPopState);
    return () => {
      document.removeEventListener('click', onClickCapture, true);
      window.removeEventListener('popstate', onPopState);
    };
  }, []);

  if (!visible) return null;

  return (
    <div
      className="pointer-events-none fixed inset-0 z-[2500] flex items-center justify-center bg-black/55 backdrop-blur-[3px]"
      role="presentation"
    >
      <LogoLoader size="lg" label="Chargement de la page" />
    </div>
  );
}
