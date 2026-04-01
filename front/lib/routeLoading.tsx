import LogoLoader from '#components/ui/LogoLoader.component';

export default function RouteLoading() {
  return (
    <div className="page-shell flex min-h-[min(70vh,640px)] w-full items-center justify-center py-16">
      <LogoLoader size="lg" label="Chargement de la page" />
    </div>
  );
}
