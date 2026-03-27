"use client";

import {useSearchParams} from "next/navigation";
import {useEffect} from "react";
import SyncLoader from "react-spinners/SyncLoader";

import {signInWithKeycloak} from "../../hooks/useAuth";

export default function LoginPage() {
  const searchParams = useSearchParams();

  useEffect(() => {
    signInWithKeycloak(searchParams.get("callbackURL") ?? "/books");
  }, [searchParams]);

  return (
    <div className="flex items-center justify-center min-h-screen">
      <SyncLoader size={8} color="#46B6BF"/>
    </div>
  );
}
