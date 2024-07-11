import { Button, ShowButtonProps, useRecordContext } from "react-admin";
import slugify from "slugify";
import VisibilityIcon from "@mui/icons-material/Visibility";

import { getItemPath } from "../../../utils/dataAccess";

export const ShowButton = () => {
  const record = useRecordContext();
  return record ? (
    <Button
      label="ra.action.show"
      // @ts-ignore
      target="_blank"
      href={getItemPath(
        {
          id: record["@id"].replace(/^\/admin\/books\//, ""),
          slug: slugify(`${record.title}-${record.author}`, {
            lower: true,
            trim: true,
            remove: /[*+~.(),;'"!:@]/g,
          }),
        },
        "/books/[id]/[slug]"
      )}
    >
      <VisibilityIcon />
    </Button>
  ) : null;
};
