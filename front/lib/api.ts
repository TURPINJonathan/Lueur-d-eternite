interface ApiGetOptions {
  /**
   * Next.js caching/revalidation (server-side).
   * - If omitted, Next.js default caching rules apply.
   */
  revalidate?: number;
}

import { headers } from 'next/headers';

async function toApiUrl(path: string): Promise<string> {
  // api.ts est destiné à être utilisé côté serveur (Server Components).
  // Si on n'a pas `NEXT_PUBLIC_BACK_BASE_URL`, on reconstruit l'origine à partir
  // des headers de la requête courante (Next rewrite possible).
  if (path.startsWith('http://') || path.startsWith('https://')) return path;

  const baseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '';
  if (baseUrl) {
    const trimmed = baseUrl.replace(/\/+$/, '');
    if (!path.startsWith('/')) return `${trimmed}/${path}`;
    return `${trimmed}${path}`;
  }

  // Fallback: origine du domaine Next (utile si backend est servi via rewrite Next).
  const h = await headers();
  const host = h.get('host');
  const proto = h.get('x-forwarded-proto') ?? 'http';
  if (!host) return path;

  const origin = `${proto}://${host}`;
  if (!path.startsWith('/')) return `${origin}/${path}`;
  return `${origin}${path}`;
}

export async function apiGet<T>(path: string, options: ApiGetOptions = {}): Promise<T> {
  const url = await toApiUrl(path);

  const res = await fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
    // Server-side fetch caching
    ...(typeof options.revalidate === 'number' ? { next: { revalidate: options.revalidate } } : {}),
  });

  if (!res.ok) {
    const text = await res.text().catch(() => '');
    throw new Error(`API GET ${url} failed (${res.status}): ${text || res.statusText}`);
  }

  return (await res.json()) as T;
}
