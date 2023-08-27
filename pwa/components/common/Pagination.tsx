import MuiPagination from "@mui/material/Pagination";
import { PaginationItem } from "@mui/material";
import Link from "next/link";

import { type PagedCollection } from "@/types/collection";
import { parsePage } from "@/utils/dataAccess";

interface Props {
  collection: PagedCollection<unknown>;
  getPagePath: (page: number) => string;
  currentPage: number;
}

export const Pagination = ({ collection, getPagePath, currentPage }: Props) => {
  const view = collection && collection["hydra:view"];
  if (!view || !view["hydra:last"]) return null;

  return (
    <div className="flex items-center justify-between bg-white mt-2 py-3 px-6" data-testid="pagination">
      <div className="mx-auto">
        <div className="flex flex-1 items-center justify-between">
          <MuiPagination count={parsePage(view["hydra:last"])} page={currentPage} siblingCount={2}
                         showFirstButton showLastButton size="large" renderItem={(item) => (
                           <PaginationItem component={Link} href={getPagePath(Number(item.page))} {...item}/>
                         )}
          />
        </div>
      </div>
    </div>
  );
};
