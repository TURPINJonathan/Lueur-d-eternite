import Image from 'next/image';

const DIMENSIONS = {
  sm: 72,
  md: 96,
  lg: 128,
} as const;

export type LogoLoaderSize = keyof typeof DIMENSIONS;

interface LogoLoaderProps {
  size?: LogoLoaderSize;
  className?: string;
  label?: string;
  showLabel?: boolean;
}

export default function LogoLoader({
  size = 'md',
  className = '',
  label = 'Chargement en cours',
  showLabel = false,
}: LogoLoaderProps) {
  const px = DIMENSIONS[size];

  return (
    <div
      role="status"
      aria-live="polite"
      aria-busy="true"
      className={`logo-loader flex flex-col items-center justify-center gap-4 ${className}`.trim()}
    >
      <span className="sr-only">{label}</span>
      <Image
        src="/assets/logo_line.webp"
        alt="Logo qui scintille en attendant le chargement..."
        width={px}
        height={px}
        className="logo-loader__image"
        priority={false}
      />
      {showLabel ? <p className="m-0 text-center text-sm font-light italic text-neutral-600">{label}</p> : null}
    </div>
  );
}
