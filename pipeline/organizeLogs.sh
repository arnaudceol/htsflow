for i in `seq 0 9`; do
for j in `seq 0 9`; do
echo $i$j;
mkdir $i$j
filter="`echo $i$j`_"
for f in `ls *$filter*`; do
 echo "$i$j: $f";
 gzip $f
 mv $f.gz $i$j/
done

done
done


for i in `seq 0 9`; do
echo $i;
#mkdir $i$j
filter="`echo $i`_"
for f in `ls *_$filter*`; do
 echo "0$i: $f";
 gzip $f
 mv $f.gz 0$i/
done

filter="`echo $i`_"
for f in `ls $filter*`; do
 echo "0$i: $f";
 gzip $f
 mv $f.gz 0$i/
done


done
