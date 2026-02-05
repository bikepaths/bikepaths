import type { Metadata } from "next";
import { Inter } from "next/font/google"; // Using Inter as requested
import "./globals.css";
import Link from 'next/link';

const inter = Inter({ subsets: ["latin"], variable: "--font-inter" });

export const metadata: Metadata = {
  title: "Bikepaths",
  description: "A hybrid digital garden.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <div className="container">
          <header>
            <Link href="/" className="logo">BIKEPATHS</Link>
            <nav>
              <ul>
                <li><Link href="/society">Society</Link></li>
                <li><Link href="/technology">Technology</Link></li>
                <li><Link href="/mind">Mind</Link></li>
              </ul>
            </nav>
          </header>
          <main>
            {children}
          </main>
        </div>
      </body>
    </html>
  );
}
