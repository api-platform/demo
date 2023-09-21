import { Button, ShowButtonProps, useRecordContext } from "react-admin";
import { getItemPath } from "@/utils/dataAccess";
import slugify from "slugify";
import VisibilityIcon from "@mui/icons-material/Visibility";

export const ShowButton = (props: ShowButtonProps) => {
  const record = useRecordContext(props);

  return record ? (
    // @ts-ignore
    <Button label={props.label} target="_blank" href={getItemPath({
      id: record["@id"].replace(/^\/admin\/books\//, ""),
      slug: slugify(`${record.title}-${record.author}`, { lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g }),
    }, "/books/[id]/[slug]")}>
      <VisibilityIcon/>
    </Button>
  ) : null;
};
ShowButton.defaultProps = { label: "ra.action.show" };
