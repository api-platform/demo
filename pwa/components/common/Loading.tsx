export const Loading = () => (
  <div className="animate-pulse flex space-x-4 h-64 p-4 w-[180px]" data-testid="loading">
    <div className="rounded-full bg-slate-700 h-10 w-[40px]"></div>
    <div className="flex-1 space-y-6 py-1">
      <div className="h-2 bg-slate-700 rounded-sm"></div>
      <div className="space-y-3">
        <div className="grid grid-cols-3 gap-4">
          <div className="h-2 bg-slate-700 rounded-sm col-span-2"></div>
          <div className="h-2 bg-slate-700 rounded-sm col-span-1"></div>
        </div>
        <div className="h-2 bg-slate-700 rounded-sm"></div>
      </div>
    </div>
  </div>
);
