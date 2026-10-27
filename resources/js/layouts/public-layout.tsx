import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import { home } from '@/routes';

export default function PublicLayout({ children }: { children: ReactNode }) {
    return (
        <div className="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
            <header className="border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80">
                <div className="mx-auto flex h-16 max-w-6xl items-center px-4 sm:px-6">
                    <Link href={home()} className="flex items-center">
                        <AppLogo />
                    </Link>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
                {children}
            </main>
        </div>
    );
}
