import type { Metadata } from "next";
import { Inter } from "next/font/google"; // Using Inter as requested
import "./globals.css";
import Link from 'next/link';

const inter = Inter({ subsets: ["latin"], variable: "--font-inter" });

export const metadata: Metadata = {
  title: "Bikepaths",
  description: "An exploration of human performance, evolutionary psychology, and the structural engineering required to solve complex societal problems.",
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
          </header>
          <main>
            {children}
          </main>
        </div>
      </body>
    </html>
  );
}
