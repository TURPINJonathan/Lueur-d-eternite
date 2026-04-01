'use client';

import { Toaster } from 'sonner';

export default function ToasterComponent() {
  return (
    <Toaster
      richColors
      position="top-right"
      toastOptions={{
        style: {
          borderRadius: '14px',
        },
      }}
    />
  );
}
