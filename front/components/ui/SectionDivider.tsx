interface Props {
  flip?: boolean;
  className?: string;
  height?: number;
}

export default function SectionDivider({ flip = false, className = '', height = 120 }: Props) {
  return (
    <div
      className={`relative ${className}`}
      style={{
        transform: flip ? 'rotate(180deg)' : undefined,
        lineHeight: 0,
      }}
      aria-hidden="true"
    >
      <svg viewBox="0 0 1440 160" width="100%" height={height} preserveAspectRatio="none">
        <defs>
          <linearGradient id="fadeGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="currentColor" stopOpacity="0.25" />
            <stop offset="100%" stopColor="currentColor" stopOpacity="0" />
          </linearGradient>
        </defs>

        <path d="M0,80 C240,140 480,20 720,80 C960,140 1200,40 1440,90 L1440,160 L0,160 Z" fill="url(#fadeGradient)" />
      </svg>
    </div>
  );
}
