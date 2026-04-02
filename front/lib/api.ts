interface ApiGetOptions {
  /**
   * Next.js caching/revalidation (server-side).
   * - If omitted, Next.js default caching rules apply.
   */
  revalidate?: number;
}

export function toAbsoluteApiUrl(path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) return path;

  const baseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL ?? 'https://api.lueur-eternite.fr';
  if (!baseUrl) {
    throw new Error(
      "NEXT_PUBLIC_BACK_BASE_URL est requis pour les appels API en export statique. Ex: 'https://api.lueur-eternite.fr'",
    );
  }

  const trimmed = baseUrl.replace(/\/+$/, '');
  const normalizedPath = path.replace(/^\/+/, '/');
  if (!path.startsWith('/')) return `${trimmed}/${path}`;
  return `${trimmed}${normalizedPath}`;
}

export async function apiGet<T>(path: string, options: ApiGetOptions = {}): Promise<T> {
  const url = toAbsoluteApiUrl(path);
  console.log(`API GET: ${url} (revalidate: ${options.revalidate ?? 'default'})`);

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
