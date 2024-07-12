import { useRecordContext, type UseRecordContextParams } from "react-admin";
import Link from "next/link";
import slugify from "slugify";

import { getItemPath } from "../../../utils/dataAccess";

export const BookField = (props: UseRecordContextParams) => {
  const record = useRecordContext(props);
  if (!record || !record.book) return null;
  return (
    <Link
      target="_blank"
      href={getItemPath(
        {
          id: record.book["@id"].replace(/^\/admin\/books\//, ""),
          slug: slugify(`${record.book.title}-${record.book.author}`, {
            lower: true,
            trim: true,
            remove: /[*+~.(),;'"!:@]/g,
          }),
        },
        "/books/[id]/[slug]"
      )}
    >
      {record.book.title} - {record.book.author}
    </Link>
  );
};
BookField.defaultProps = { label: "Book" };
