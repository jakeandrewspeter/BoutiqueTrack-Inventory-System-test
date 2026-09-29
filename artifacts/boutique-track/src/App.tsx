import { useEffect, useRef, useState, type ReactNode } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ErrorBoundary } from '@/components/error-boundary';
import { Toaster } from '@/components/ui/toaster';
import { TooltipProvider } from '@/components/ui/tooltip';
import NotFound from '@/pages/not-found';
import { ArrowUpRight, CircleCheck, Expand, LockKeyhole, RefreshCw, Store, X } from 'lucide-react';
import {
  Route,
  Switch,
  useLocation,
  Router as WouterRouter,
} from 'wouter';

const queryClient = new QueryClient();

function Home() {
  const [loaded, setLoaded] = useState(false);
  const [refreshKey, setRefreshKey] = useState(0);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const frameShellRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const syncFullscreen = () => setIsFullscreen(document.fullscreenElement === frameShellRef.current);
    document.addEventListener('fullscreenchange', syncFullscreen);
    return () => document.removeEventListener('fullscreenchange', syncFullscreen);
  }, []);

  const refreshWorkspace = () => {
    setLoaded(false);
    setRefreshKey((current) => current + 1);
  };

  const toggleFullscreen = async () => {
    if (!frameShellRef.current) return;
    try {
      if (document.fullscreenElement) {
        await document.exitFullscreen();
      } else {
        await frameShellRef.current.requestFullscreen();
      }
    } catch {
      // Fullscreen is optional; the embedded workspace remains usable without it.
    }
  };

  return (
    <main className="host" data-testid="host-boutique-track">
      <header className="masthead">
        <div className="brand-lockup">
          <div className="brand-mark" aria-hidden="true">b</div>
          <div>
            <div className="brand-name">BoutiqueTrack</div>
            <div className="brand-caption">The store, in sync</div>
          </div>
        </div>
        <div className="masthead-meta" aria-label="BoutiqueTrack workspace">
          <span>RETAIL OPERATIONS</span>
          <span className="meta-divider" />
          <span className="masthead-detail">PRIVATE STORE WORKSPACE</span>
        </div>
      </header>

      <section className="workspace" aria-label="Live BoutiqueTrack workspace">
        <div className="workspace-bar">
          <div className="workspace-heading">
            <Store size={17} strokeWidth={1.8} color="#6c816e" aria-hidden="true" />
            <div>
              <div className="workspace-kicker">Store workspace</div>
              <div className="workspace-title">Your boutique, at a glance</div>
            </div>
          </div>
          <div className="workspace-tools">
            <div className="connection-state" aria-live="polite" data-testid="status-workspace">
              {loaded ? <CircleCheck size={14} strokeWidth={1.8} aria-hidden="true" /> : <span className="connection-dot is-loading" />}
              <span>{loaded ? 'Live workspace' : 'Connecting'}</span>
            </div>
            <button
              type="button"
              className="host-button"
              onClick={refreshWorkspace}
              aria-label="Reload workspace"
              title="Reload workspace"
              data-testid="button-reload-workspace"
            >
              <RefreshCw size={14} strokeWidth={1.8} aria-hidden="true" />
              <span className="host-button-label">Reload</span>
            </button>
            <button
              type="button"
              className="host-button"
              onClick={toggleFullscreen}
              aria-label={isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'}
              title={isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'}
              data-testid="button-toggle-fullscreen"
            >
              {isFullscreen ? <X size={14} strokeWidth={1.8} aria-hidden="true" /> : <Expand size={14} strokeWidth={1.8} aria-hidden="true" />}
              <span className="host-button-label">{isFullscreen ? 'Exit' : 'Expand'}</span>
            </button>
            <a
              className="host-button host-button-strong"
              href="/php/"
              target="_blank"
              rel="noreferrer"
              aria-label="Open BoutiqueTrack in a new tab"
              title="Open in a new tab"
              data-testid="link-open-workspace"
            >
              <ArrowUpRight size={14} strokeWidth={1.8} aria-hidden="true" />
              <span className="host-button-label">Open separately</span>
            </a>
          </div>
        </div>

        <div className="frame-shell" ref={frameShellRef} data-testid="frame-shell">
          <div className="frame-chrome" aria-hidden="true">
            <div className="chrome-lights">
              <span className="chrome-light" />
              <span className="chrome-light" />
              <span className="chrome-light" />
            </div>
            <div className="address-field">
              <LockKeyhole size={11} strokeWidth={1.8} />
              <span>Local store · /php/</span>
            </div>
            <span className="frame-indicator">Live application</span>
          </div>
          <div className="frame-stage">
            <div className={`frame-loading${loaded ? ' is-hidden' : ''}`} aria-hidden={loaded}>
              <div className="loading-card">
                <div className="loading-symbol"><Store size={17} strokeWidth={1.8} aria-hidden="true" /></div>
                <div className="loading-title">Opening your store</div>
                <div className="loading-copy">Connecting to the live BoutiqueTrack workspace.</div>
                <div className="loading-line" />
              </div>
            </div>
            <iframe
              key={refreshKey}
              className="live-frame"
              src="/php/"
              title="BoutiqueTrack live store application"
              onLoad={() => setLoaded(true)}
              data-testid="iframe-live-boutique-track"
              allow="clipboard-read; clipboard-write"
            />
          </div>
        </div>
      </section>
    </main>
  );
}

function Router() {
  return (
    // Keep a shared shell (sidebar, navbar) outside the boundary so it
    // survives a page crash.
    <RoutedErrorBoundary>
      <Switch>
        <Route path="/" component={Home} />
        <Route component={NotFound} />
      </Switch>
    </RoutedErrorBoundary>
  );
}

function RoutedErrorBoundary({ children }: { children: ReactNode }) {
  const [location] = useLocation();
  return <ErrorBoundary resetKey={location}>{children}</ErrorBoundary>;
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <WouterRouter base={import.meta.env.BASE_URL.replace(/\/$/, '')}>
          <Router />
        </WouterRouter>
        <Toaster />
      </TooltipProvider>
    </QueryClientProvider>
  );
}

export default App;
