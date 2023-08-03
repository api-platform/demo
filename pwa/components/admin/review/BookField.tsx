import { useRecordContext, type UseRecordContextParams } from "react-admin";
import Link from "next/link";
import { getItemPath } from "@/utils/dataAccess";
import slugify from "slugify";

export const BookField = (props: UseRecordContextParams) => {
  const record = useRecordContext(props);

  return !!record && !!record.book ? (
    <Link target="_blank" href={getItemPath({
      id: record.book["@id"].replace(/^\/admin\/books\//, ""),
      slug: slugify(`${record.book.title}-${record.book.author}`, { lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g }),
    }, "/books/[id]/[slug]")}>
      {record.book.title} - {record.book.author}
    </Link>
  ) : null;
};
BookField.defaultProps = { label: "Book" };
