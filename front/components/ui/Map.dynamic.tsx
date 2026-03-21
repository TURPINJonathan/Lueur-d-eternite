'use client';

import dynamic from 'next/dynamic';

export default dynamic(() => import('./Map.component'), {
  ssr: false,
  loading: () => (
    <div
      className="flex h-[360px] w-full items-center justify-center rounded-[1.5rem] bg-[linear-gradient(180deg,rgba(255,255,255,0.88),rgba(244,237,228,0.78))] text-sm text-neutral-500"
      aria-hidden
    >
      Chargement de la carte…
    </div>
  ),
});
