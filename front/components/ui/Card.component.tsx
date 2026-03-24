import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

interface CardComponentProps {
  icon?: LucideIcon;
  title?: string;
  description?: string;
  children?: ReactNode;
  premium?: boolean;
  className?: string;
}

export default function CardComponent({
  icon: Icon,
  title,
  description,
  children,
  premium = false,
  className = '',
}: CardComponentProps) {
  return (
    <div
      className={`${premium ? 'feature-card--premium' : 'feature-card'} flex flex-col items-center justify-center gap-3 ${className}`.trim()}
    >
      {Icon && <Icon size={40} aria-hidden />}
      {title && <h4 className="!text-lg font-semibold">{title}</h4>}
      {description && <p className="text-sm text-center italic">{description}</p>}
      {children}
    </div>
  );
}
