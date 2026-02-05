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
            <Link href="https://bikepaths.org/blog" className="logo">bikepaths.org</Link>
            <nav>
              <ul>
                <li><Link href="https://bikepaths.org/blog/category/society">Society</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/technology">Technology</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/systems">Systems</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/society">Society</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/adventure">Adventure</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/health">Health</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/history">History</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/money">Money</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/skills">Skills</Link></li>
                <li><Link href="https://bikepaths.org/blog/category/nature">Nature</Link></li>
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
